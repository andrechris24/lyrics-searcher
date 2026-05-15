<?php

namespace App;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

trait CrudPermissionTrait
{
	// the operations defined for CRUD controller
	public array $operations = ['list', 'show', 'create', 'update', 'delete'];

	/**
	 * set CRUD access using spatie Permissions defined for logged in user
	 *
	 * @return void
	 */
	public function setAccessUsingPermissions()
	{
		// default
		$this->crud->denyAccess($this->operations);

		// get context
		$table = CRUD::getModel()->getTable();
		$user = backpack_user();

		// double check if no authenticated user
		if (!$user) {
			return; // allow nothing
		}

		// enable operations depending on permission
		foreach (
			[
				// permission level => [crud operations]
				'see' => ['list', 'show'], // e.g. permission 'users.see' allows to display users
				'edit' => ['list', 'show', 'create', 'update', 'delete'] // e.g. 'users.edit' permission allows all operations
			] as $level => $operations
		) {
			if ($user->can("$table.$level")) {
				$this->crud->allowAccess($operations);
			}
		}
	}

	public function setListByPermission()
	{
		$table = CRUD::getModel()->getTable();
		$user = backpack_user();
		if (!$user) return;
		else if ($user->cannot("$table.viewall"))
			$this->crud->addBaseClause('where', 'user_id', '=', $user->id);
	}
}
