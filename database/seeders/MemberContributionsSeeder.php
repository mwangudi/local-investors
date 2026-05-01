<?php

namespace Database\Seeders;

use Database\Seeders\Members\CatherineMasindeSeeder;
// As you provide each member's records, add their seeder here:
// use Database\Seeders\Members\MichaelWangudiSeeder;
// use Database\Seeders\Members\VioletKamadiSeeder;
// use Database\Seeders\Members\JosephSifunaSeeder;
// use Database\Seeders\Members\TracyMuendiSeeder;
// use Database\Seeders\Members\SymonPeterNgatiaSeeder;
// use Database\Seeders\Members\TorryMabaleSeeder;
// use Database\Seeders\Members\MikeCSeeder;
// use Database\Seeders\Members\AbigailNjokiSeeder;
// use Database\Seeders\Members\CharlesKingoriSeeder;
// use Database\Seeders\Members\StellaMutheuSeeder;
// use Database\Seeders\Members\ScolasticaMuswiiSeeder;
// use Database\Seeders\Members\NaomiNyorokaSeeder;
// use Database\Seeders\Members\KavinyaOduorSeeder;
// use Database\Seeders\Members\SusanNginaMuswiiSeeder;
use Illuminate\Database\Seeder;

/**
 * Orchestrates per-member contribution seeders.
 *
 * Run after MembersAndContributionsSeeder (which creates the Member rows).
 * Each member seeder owns its own contribution history end-to-end.
 */
class MemberContributionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatherineMasindeSeeder::class,
            // MichaelWangudiSeeder::class,
            // VioletKamadiSeeder::class,
            // JosephSifunaSeeder::class,
            // TracyMuendiSeeder::class,
            // SymonPeterNgatiaSeeder::class,
            // TorryMabaleSeeder::class,
            // MikeCSeeder::class,
            // AbigailNjokiSeeder::class,
            // CharlesKingoriSeeder::class,
            // StellaMutheuSeeder::class,
            // ScolasticaMuswiiSeeder::class,
            // NaomiNyorokaSeeder::class,
            // KavinyaOduorSeeder::class,
            // SusanNginaMuswiiSeeder::class,
        ]);
    }
}
