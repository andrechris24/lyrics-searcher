<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\{Artisan, Http, Log};

Artisan::command('inspire', function () {
	$this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Artisan::command('usertoken', function () {
	$musixmatch = Http::get('https://apic-desktop.musixmatch.com/ws/1.1/token.get', [
		'user_language' => 'en',
		'app_id' => 'web-desktop-app-v1.0'
	])->throw();
	$r = json_decode($musixmatch->body(), true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		Log::error(
			'Invalid JSON response for ' . $musixmatch->body() . ': ' . json_last_error_msg()
		);
		abort(500, 'Error parsing response: ' . json_last_error_msg());
	}
	$header = $r['message']['header'];
	abort_if(
		$header['status_code'] !== 200,
		$header['status_code'],
		array_key_exists('hint', $header) ? $header['hint'] : 'Musixmatch HTTP Error ' . $header['status_code']
	);
	$body = $r['message']['body'];
	if (array_key_exists('user_token', $body)) {
		if ($body['user_token'] === 'UpgradeOnlyUpgradeOnlyUpgradeOnlyUpgradeOnly') {
			throw new Exception(
				message: "Failed to retrieve Musixmatch token, please try again in a few minutes.",
				previous: new Exception($body['user_token'])
			);
		}
		echo "Open env file, then set MUSIXMATCH_TOKEN value to:\n" . $body['user_token'];
	} else abort(404, 'No user token provided from Musixmatch');
})->purpose('Generates Musixmatch token');
