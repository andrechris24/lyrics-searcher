<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Backpack\PermissionManager\app\Models\Permission;
use Backpack\PermissionManager\app\Models\Role;

class PermissionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// create permission for each combination of table.level
		collect([ // tables
			'users',
			'roles',
			'lyrics'
		])->crossJoin([ // levels
			'see',
			'edit',
			'viewall'
		])->each(
			fn(array $item) => Permission::firstOrCreate([
				'name' => implode('.', $item)
			])->save()
		);
		User::first()
			->givePermissionTo(['users.edit', 'roles.edit', 'lyrics.edit', 'lyrics.viewall']);
	}
}
