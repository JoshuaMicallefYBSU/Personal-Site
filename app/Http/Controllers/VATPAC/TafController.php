<?php

namespace App\Http\Controllers\VATPAC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TafController extends Controller
{
    public function show(Request $request)
    {
        $ids = strtoupper(trim($request->query('ids', '')));
        $icaos = array_filter(array_map('trim', explode(',', $ids)));

        if (empty($icaos)) {
            return $this->xmlResponse(
                $this->errorXml('Invalid ICAO: missing'),
                400
            );
        }

        foreach ($icaos as $icao) {
            if (!preg_match('/^[A-Z]{4}$/', $icao)) {
                return $this->xmlResponse(
                    $this->errorXml("Invalid ICAO: {$this->escapeXml($icao)}"),
                    400
                );
            }
        }

        try {
            $upstream = Http::withHeaders([
                'User-Agent' => 'vatSys-TAF-XML-Laravel',
            ])
            ->timeout(10)
            ->get('https://wx.vatpac.org/tafs.txt');

            if (!$upstream->successful()) {
                return $this->xmlResponse(
                    $this->errorXml("Upstream fetch failed with status {$upstream->status()}"),
                    502
                );
            }

            $text = $upstream->body();
            $results = [];

            foreach ($icaos as $icao) {
                $taf = $this->extractTaf($text, $icao);

                if ($taf) {
                    $results[] = [
                        'icao' => $icao,
                        'taf'  => $taf,
                    ];
                }
            }

            if (empty($results)) {
                $this->sendDiscordWebhook(
                    "TAF API called at " . Carbon::now('UTC')->format('H:i') . "Z\n> No TAF found for: " . implode(', ', $icaos)
                );

                return $this->xmlResponse(
                    $this->errorXml('No TAF found for requested ICAOs'),
                    404
                );
            }

            $stationsXml = '';

            foreach ($results as $res) {
                $stationsXml .= "
  <station>
    <id>{$this->escapeXml($res['icao'])}</id>
    <raw_text>{$this->escapeXml($res['taf'])}</raw_text>
  </station>";
            }

            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response>
{$stationsXml}
</response>
XML;

            $discordMessage = "# TAF API called at " . Carbon::now('UTC')->format('H:i') . "Z\n";

            foreach ($results as $res) {
                $discordMessage .= "**{$res['icao']}**\n> {$res['taf']}\n";
            }

            $this->sendDiscordWebhook($discordMessage);

            return $this->xmlResponse($xml, 200);

        } catch (\Throwable $e) {
            return $this->xmlResponse(
                $this->errorXml('Worker exception: ' . $this->escapeXml($e->getMessage())),
                500
            );
        }
    }

    private function extractTaf(string $text, string $icao): ?string
    {
        $src = str_replace("\r", '', $text);

        $blocks = preg_split('/(?=^TAF\s)/m', $src);
        $blocks = array_filter(array_map('trim', $blocks));

        foreach ($blocks as $block) {
            $singleLine = preg_replace('/\s+/', ' ', str_replace("\n", ' ', $block));
            $singleLine = trim($singleLine);

            if (preg_match('/^TAF\s+(?:AMD\s+)?' . preg_quote($icao, '/') . '\b/i', $singleLine)) {
                return $singleLine;
            }
        }

        return null;
    }

    private function escapeXml(string $str): string
    {
        return str_replace(
            ['&', '<', '>', '"', "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'],
            $str
        );
    }

    private function errorXml(string $message): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response>
  <error>{$message}</error>
</response>
XML;
    }

    private function xmlResponse(string $body, int $status)
    {
        return response($body, $status)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=120');
    }

    private function sendDiscordWebhook(string $message): void
    {
        try {
            Http::post("https://canary.discord.com/api/webhooks/1485860377278550126/huRahVetYfoWxWDfRz1ciLea5nv1MlAYDLHOIjdC4CUtbUe_28iifi8dH2c8juh1cnkR", [
                'content' => $message,
            ]);
        } catch (\Throwable $e) {
            // swallow errors so API never breaks
        }
    }
}