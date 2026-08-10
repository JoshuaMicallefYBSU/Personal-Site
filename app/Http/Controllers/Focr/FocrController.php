<?php

namespace App\Http\Controllers\Focr;

use App\Http\Controllers\Controller;
use App\Models\MovieRequest;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FocrController extends Controller
{
    private const TYPES = ['Movie', 'Series', 'TV Show'];

    public function index(): View
    {
        return view('focr.index', [
            'types' => self::TYPES,
            'requests' => MovieRequest::orderByRaw("FIELD(status, 'Available', 'Requested')")
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'type' => ['required', Rule::in(self::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'release_year' => ['required', 'integer', 'min:1878', 'max:' . (date('Y') + 5)],
            'all_episodes' => ['sometimes', 'boolean'],
            'episodes' => [
                Rule::requiredIf(fn () => $request->input('type') !== 'Movie' && ! $request->boolean('all_episodes')),
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $isEpisodic = $validated['type'] !== 'Movie';
        $allEpisodes = $isEpisodic && $request->boolean('all_episodes');

        $movieRequest = MovieRequest::create([
            'name' => $validated['name'] ?? null,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'release_year' => $validated['release_year'],
            'all_episodes' => $allEpisodes,
            'episodes' => $isEpisodic && ! $allEpisodes ? $validated['episodes'] : null,
            'status' => MovieRequest::STATUS_REQUESTED,
            'delete_token' => Str::random(48),
            'available_token' => Str::random(48),
        ]);

        $this->notifyDiscord($movieRequest);

        return back()->with('focr_status', 'success');
    }

    public function markAvailable(string $token): View
    {
        $movieRequest = MovieRequest::where('available_token', $token)->firstOrFail();

        if ($movieRequest->status === MovieRequest::STATUS_REQUESTED) {
            $movieRequest->update(['status' => MovieRequest::STATUS_AVAILABLE]);
            $this->updateDiscordMessage($movieRequest);
        }

        return view('focr.available', [
            'title' => $movieRequest->title,
        ]);
    }

    public function destroy(string $token): View
    {
        $movieRequest = MovieRequest::where('delete_token', $token)->firstOrFail();

        $this->deleteDiscordMessage($movieRequest);
        $movieRequest->delete();

        return view('focr.deleted', [
            'title' => $movieRequest->title,
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        $key = config('services.focr.redirect_key');
        $url = config('services.focr.redirect_url');

        if (! $key || ! $url || ! hash_equals($key, (string) $request->query('key'))) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->notifyRedirectAccess($request);

        return redirect()->away($url);
    }

    private function notifyDiscord(MovieRequest $movieRequest): void
    {
        $response = $this->postToWebhook([$this->buildRequestEmbed($movieRequest)], wait: true);

        $messageId = $response?->json('id');

        if ($messageId) {
            $movieRequest->update(['discord_message_id' => $messageId]);
        }
    }

    private function updateDiscordMessage(MovieRequest $movieRequest): void
    {
        if (! $movieRequest->discord_message_id) {
            return;
        }

        $webhookUrl = config('services.focr.discord_webhook_url');

        if (! $webhookUrl) {
            return;
        }

        Http::patch("{$webhookUrl}/messages/{$movieRequest->discord_message_id}", [
            'embeds' => [$this->buildRequestEmbed($movieRequest)],
        ]);
    }

    private function deleteDiscordMessage(MovieRequest $movieRequest): void
    {
        if (! $movieRequest->discord_message_id) {
            return;
        }

        $webhookUrl = config('services.focr.discord_webhook_url');

        if (! $webhookUrl) {
            return;
        }

        Http::delete("{$webhookUrl}/messages/{$movieRequest->discord_message_id}");
    }

    private function buildRequestEmbed(MovieRequest $movieRequest): array
    {
        $fields = [
            ['name' => 'Requested By', 'value' => $movieRequest->name ?? 'Anonymous', 'inline' => true],
            ['name' => 'Type', 'value' => $movieRequest->type, 'inline' => true],
            ['name' => 'Release Year', 'value' => (string) $movieRequest->release_year, 'inline' => true],
            ['name' => 'Status', 'value' => $movieRequest->status, 'inline' => true],
        ];

        if ($movieRequest->type !== 'Movie') {
            $fields[] = [
                'name' => 'Episodes',
                'value' => $movieRequest->all_episodes ? 'All seasons/episodes' : $movieRequest->episodes,
                'inline' => true,
            ];
        }

        if ($movieRequest->status === MovieRequest::STATUS_AVAILABLE) {
            $deleteUrl = route('focr.destroy', $movieRequest->delete_token);

            return [
                'title' => 'Available: ' . $movieRequest->title,
                'color' => 0x22c55e,
                'fields' => $fields,
                'description' => "✅ Now available. [Remove this request]({$deleteUrl})",
            ];
        }

        $availableUrl = route('focr.available', $movieRequest->available_token);

        return [
            'title' => 'New Request: ' . $movieRequest->title,
            'color' => 0x6366f1,
            'fields' => $fields,
            'description' => "[Mark as available]({$availableUrl})",
        ];
    }

    private function notifyRedirectAccess(Request $request): void
    {
        $this->postToWebhook([[
            'title' => 'Redirect link accessed',
            'color' => 0xf59e0b,
            'fields' => [
                ['name' => 'IP Address', 'value' => $request->ip(), 'inline' => true],
                ['name' => 'User Agent', 'value' => $request->userAgent() ?: 'Unknown', 'inline' => false],
            ],
        ]], webhookUrl: config('services.focr.discord_second_webhook_url'), mention: false);
    }

    private function postToWebhook(array $embeds, bool $wait = false, ?string $webhookUrl = null, bool $mention = true): ?HttpResponse
    {
        $webhookUrl ??= config('services.focr.discord_webhook_url');

        if (! $webhookUrl) {
            Log::warning('FOCR Discord webhook URL is not set; skipping Discord notification.');

            return null;
        }

        $userId = $mention ? config('services.focr.discord_user_id') : null;

        return Http::post($wait ? "{$webhookUrl}?wait=true" : $webhookUrl, [
            'content' => $userId ? "<@{$userId}>" : null,
            'allowed_mentions' => ['users' => $userId ? [$userId] : []],
            'embeds' => $embeds,
        ]);
    }
}
