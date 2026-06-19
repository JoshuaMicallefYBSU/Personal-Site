<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AirportSeeder extends Seeder
{
    /**
     * OpenFlights airports.dat — 14-column CSV, no header row.
     *
     * Column indices (0-based):
     *   0  - Internal ID
     *   1  - Name
     *   2  - City
     *   3  - Country
     *   4  - IATA code
     *   5  - ICAO code
     *   6  - Latitude
     *   7  - Longitude
     *   8  - Altitude (ft)
     *   9  - UTC offset (hours)
     *   10 - DST rule
     *   11 - Timezone
     *   12 - Type
     *   13 - Source
     */
    protected string $url = 'https://raw.githubusercontent.com/jpatokal/openflights/master/data/airports.dat';

    /** How many rows to upsert per DB batch. */
    protected int $chunkSize = 500;

    public function run(): void
    {
        $this->command->info("Fetching airports data from: {$this->url}");

        $response = Http::timeout(30)->get($this->url);

        if (! $response->successful()) {
            $this->command->error("Failed to fetch airport data (HTTP {$response->status()}).");
            return;
        }

        $lines  = explode("\n", $response->body());
        $batch  = [];
        $total  = 0;
        $skipped = 0;
        $now    = now();

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $cols = str_getcsv($line);

            // Expect at least 8 columns (through longitude).
            if (count($cols) < 8) {
                $skipped++;
                continue;
            }

            $icao = trim($cols[5]);
            $lat  = trim($cols[6]);
            $lon  = trim($cols[7]);
            $name = trim($cols[1]);

            // Skip rows with no valid ICAO code or coordinates.
            if ($icao === '' || $icao === '\\N' || $lat === '' || $lon === '') {
                $skipped++;
                continue;
            }

            $batch[] = [
                'icao'       => $icao,
                'name'       => $name,
                'latitude'   => (float) $lat,
                'longitude'  => (float) $lon,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $this->chunkSize) {
                $this->upsertBatch($batch);
                $total += count($batch);
                $batch  = [];
            }
        }

        // Flush remaining rows.
        if (! empty($batch)) {
            $this->upsertBatch($batch);
            $total += count($batch);
        }

        $this->command->info("Seeded {$total} airports into rcl_airports. Skipped {$skipped} invalid rows.");
    }

    /**
     * Upsert a batch of airport rows.
     * On duplicate ICAO code, refresh name and coordinates.
     */
    protected function upsertBatch(array $rows): void
    {
        DB::table('rcl_airports')->upsert(
            $rows,
            ['icao'],                          // unique key
            ['name', 'latitude', 'longitude', 'updated_at']  // columns to update on conflict
        );
    }
}