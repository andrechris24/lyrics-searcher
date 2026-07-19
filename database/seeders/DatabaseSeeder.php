<?php

namespace Database\Seeders;

use App\Models\{User,Lyric};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		User::factory()->create([
			'name' => 'Test User',
			'email' => 'test@example.com'
		]);
		Lyric::factory(28)->create();
		$this->call([PermissionSeeder::class]);
	}
}
