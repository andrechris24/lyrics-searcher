<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{
	use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
	use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
	use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
	use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
	use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
	use \App\CrudPermissionTrait;

	/**
	 * Configure the CrudPanel object. Apply settings to all operations.
	 *
	 * @return void
	 */
	public function setup()
	{	
		parent::setup();
		CRUD::setModel(\App\Models\User::class);
		CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
		CRUD::setEntityNameStrings('user', 'users');
    $this->setAccessUsingPermissions();
	}

	/**
	 * Define what happens when the List operation is loaded.
	 *
	 * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
	 * @return void
	 */
	protected function setupListOperation()
	{
		CRUD::setFromDb(); // set columns from db columns.

		/**
		 * Columns can be defined using the fluent syntax:
		 * - CRUD::column('price')->type('number');
		 */
	}

	/**
	 * Define what happens when the Create operation is loaded.
	 *
	 * @see https://backpackforlaravel.com/docs/crud-operation-create
	 * @return void
	 */
	protected function setupCreateOperation()
	{
		CRUD::field('name')->validationRules('required|min:5');
		CRUD::field('email')->validationRules('required|email|unique:users,email');
		CRUD::field('password')->validationRules('required');

		/**
		 * Fields can be defined using the fluent syntax:
		 * - CRUD::field('price')->type('number');
		 */
	}

	/**
	 * Define what happens when the Update operation is loaded.
	 *
	 * @see https://backpackforlaravel.com/docs/crud-operation-update
	 * @return void
	 */
	protected function setupUpdateOperation()
	{
		CRUD::field('name')->validationRules('required|min:5');
		CRUD::field('email')->validationRules('required|email|unique:users,email,' . CRUD::getCurrentEntryId());
		CRUD::field('password')->hint('Type a password to change it.');
	}
}
