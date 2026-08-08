<?php

namespace App\Http\Controllers\Focr;

use App\Http\Controllers\Controller;
use App\Models\MovieRequest;
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
            'requests' => MovieRequest::latest()->get(),
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
            'delete_token' => Str::random(48),
        ]);

        $this->notifyDiscord($movieRequest);

        return back()->with('focr_status', 'success');
    }

    public function destroy(string $token): View
    {
        $movieRequest = MovieRequest::where('delete_token', $token)->firstOrFail();
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
        $deleteUrl = route('focr.destroy', $movieRequest->delete_token);

        $fields = [
            ['name' => 'Requested By', 'value' => $movieRequest->name ?? 'Anonymous', 'inline' => true],
            ['name' => 'Type', 'value' => $movieRequest->type, 'inline' => true],
            ['name' => 'Release Year', 'value' => (string) $movieRequest->release_year, 'inline' => true],
        ];

        if ($movieRequest->type !== 'Movie') {
            $fields[] = [
                'name' => 'Episodes',
                'value' => $movieRequest->all_episodes ? 'All seasons/episodes' : $movieRequest->episodes,
                'inline' => true,
            ];
        }

        $this->postToWebhook([[
            'title' => 'New Request: ' . $movieRequest->title,
            'color' => 0x6366f1,
            'fields' => $fields,
            'description' => "[Click here to delete this request]({$deleteUrl})",
        ]]);
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
        ]]);
    }

    private function postToWebhook(array $embeds): void
    {
        $webhookUrl = config('services.focr.discord_webhook_url');

        if (! $webhookUrl) {
            Log::warning('FOCR_DISCORD_WEBHOOK_URL is not set; skipping Discord notification.');

            return;
        }

        $userId = config('services.focr.discord_user_id');

        Http::post($webhookUrl, [
            'content' => $userId ? "<@{$userId}>" : null,
            'allowed_mentions' => ['users' => $userId ? [$userId] : []],
            'embeds' => $embeds,
        ]);
    }
}
