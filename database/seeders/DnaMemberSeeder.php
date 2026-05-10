<?php

namespace Database\Seeders;

use App\Services\DnaMemberImportService;
use Illuminate\Database\Seeder;

class DnaMemberSeeder extends Seeder
{
    public function run(): void
    {
        app(DnaMemberImportService::class)->import(
            database_path('seeders/data/dna_members.csv')
        );
    }
}
