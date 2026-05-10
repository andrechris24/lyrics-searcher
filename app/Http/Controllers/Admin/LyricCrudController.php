<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class LyricCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LyricCrudController extends CrudController
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
		CRUD::setModel(\App\Models\Lyric::class);
		CRUD::setRoute(config('backpack.base.route_prefix') . '/lyric');
		CRUD::setEntityNameStrings('lyric', 'lyrics');
		$this->setAccessUsingPermissions();
		$this->setListByPermission();
	}

	/**
	 * Define what happens when the List operation is loaded.
	 *
	 * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
	 * @return void
	 */
	protected function setupListOperation()
	{
		CRUD::column('title');
		CRUD::column('artist');
		CRUD::column('album');
		CRUD::column('duration')->type('model_function')->function_name('showRealDuration');
		CRUD::column('user_id')->entity('user')->model('App\Models\User')->attribute('name');
		CRUD::column('offset')->type('number');

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
		CRUD::group(CRUD::field('title'), CRUD::field('artist'))
			->attributes(['required' => true])->validationRules('required');
		CRUD::field('album');
		CRUD::group(
			CRUD::group(
				CRUD::field('minutes')->attributes(['required' => true, 'min' => 0, 'max' => 199])
					->validationRules('required|numeric|between:0,199'),
				CRUD::field('seconds')->attributes(['required' => true, 'min' => 0, 'max' => 59])
					->validationRules('required|numeric|between:0,59')
			)->fake(true)->store_in('duration'),
			CRUD::field('offset')->validationRules('nullable|integer')
		)->type('number')->default(0);
		CRUD::field('content')->type('textarea')->attributes(['rows' => 20, 'required' => true])
			->validationRules('required|min:50');
		CRUD::field('user_id')->type('hidden')->value(backpack_user()->id);

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
		$entries = json_decode(CRUD::getCurrentEntry(), true);
		CRUD::group(CRUD::field('title'), CRUD::field('artist'))
			->attributes(['required' => true])->validationRules('required');
		CRUD::field('album');
		CRUD::group(
			CRUD::group(
				CRUD::field('minutes')->attributes(['required' => true, 'min' => 0, 'max' => 199])
					->value(floor($entries['duration'] / 60))
					->validationRules('required|numeric|between:0,199'),
				CRUD::field('seconds')->attributes(['required' => true, 'min' => 0, 'max' => 59])
					->value($entries['duration'] % 60)
					->validationRules('required|numeric|between:0,59')
			)->fake(true)->store_in('duration'),
			CRUD::field('offset')->validationRules('nullable|integer')
		)->type('number')->default(0);
		CRUD::field('content')->type('textarea')->attributes(['rows' => 20])
			->validationRules('required|min:50');
		// CRUD::field('user_id')->type('hidden');
	}

	protected function setupShowOperation()
	{
		$this->setupListOperation();
		CRUD::column('content')->type('textarea');
	}
}
