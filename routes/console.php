<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\{Artisan, Http};

Artisan::command('inspire', function () {
	$this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Artisan::command('usertoken', function () {
	$musixmatch = Http::get('https://apic-desktop.musixmatch.com/ws/1.1/token.get', [
		'user_language' => 'en',
		'app_id' => 'web-desktop-app-v1.0'
	])->throw();
	$r = $musixmatch->json(null, null, JSON_THROW_ON_ERROR);
	$header = $r['message']['header'];
	abort_if(
		$header['status_code'] !== 200,
		$header['status_code'],
		array_key_exists('hint', $header) ? $header['hint'] : "Musixmatch HTTP Error {$header['status_code']}"
	);
	$body = $r['message']['body'];
	if (array_key_exists('user_token', $body)) {
		if ($body['user_token'] === 'UpgradeOnlyUpgradeOnlyUpgradeOnlyUpgradeOnly') {
			throw new Exception(
				message: "Failed to retrieve Musixmatch token, please try again in a few minutes.",
				previous: new Exception($body['user_token'])
			);
		}
		$this->comment("<options=bold>Open env file, then set MUSIXMATCH_TOKEN value to:</>");
		$this->info($body['user_token']);
		$this->question("\nIf you use AIMP, replace <MX_TOKEN_X> placeholders inside aimp_webLyrics.ini with above value before replacing original file.");
		$this->warn("Don't use same token for all placeholders including env due to strict rate limit.");
	} else abort(404, 'No user token provided from Musixmatch');
})->purpose('Generates Musixmatch token');
