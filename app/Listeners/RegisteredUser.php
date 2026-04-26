<?php

namespace App\Listeners;

// use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RegisteredUser
{
	/**
	 * Create the event listener.
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 */
	public function handle(Registered $event): void
	{
		$user = $event->user;
		$user->givePermissionTo('lyrics.edit');
	}
}
