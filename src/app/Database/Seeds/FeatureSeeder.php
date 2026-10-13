<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

/**
 * Сидирование пользователей и комментариев для функционала.
 */
class FeatureSeeder extends Seeder
{
    private const USERS_COUNT = 3;

    private const COMMENTS_COUNT = 50;

    /**
     * @return void
     */
    public function run(): void
    {
        $faker = Factory::create('ru_RU');

        $userIds = [];

        for ($i = 0; $i < self::USERS_COUNT; $i++) {
            $this->db->table('users')->insert([
                'email' => $faker->unique()->safeEmail(),
                'session' => $faker->unique()->sha1(),
            ]);

            $userIds[] = (int) $this->db->insertID();
        }

        for ($i = 0; $i < self::COMMENTS_COUNT; $i++) {
            $this->db->table('comments')->insert([
                'user_id' => $faker->randomElement($userIds),
                'text' => $faker->realText(mt_rand(40, 200)),
            ]);
        }
    }
}
