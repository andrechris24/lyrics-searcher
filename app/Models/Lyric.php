<?php

namespace App\Models;

use App\Casts\AsSecond;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lyric extends Model
{
	use CrudTrait, HasFactory;

	/*
	|--------------------------------------------------------------------------
	| GLOBAL VARIABLES
	|--------------------------------------------------------------------------
	*/

	protected $table = 'lyrics';
	// protected $primaryKey = 'id';
	// public $timestamps = false;
	protected $guarded = ['id'];
	// protected $fillable = [];
	// protected $hidden = [];

	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	public function showRealDuration(): string
	{
		return gmdate("i:s", $this->duration);
	}

	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function casts()
	{
		return ['duration' => AsSecond::class];
	}
}
