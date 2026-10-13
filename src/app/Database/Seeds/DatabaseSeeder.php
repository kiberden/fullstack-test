<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Точка входа для сидирования базы.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $this->call(FeatureSeeder::class);
    }
}
