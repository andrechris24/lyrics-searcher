<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\{Artisan,Http};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Artisan::command('usertoken', function(){
    $musixmatch = Http::get('https://apic-desktop.musixmatch.com/ws/1.1/token.get', [
        'user_language' => 'en',
        'app_id' => 'web-desktop-app-v1.0'
    ]);
    $r = json_decode($musixmatch->body(), true);
    if (json_last_error() !== JSON_ERROR_NONE) 
        abort(500, 'Error parsing JSON response: ' . json_last_error_msg());
    $header = $r['message']['header'];
    if ($header['status_code'] !== 200) 
        abort($header['status_code'], $header['hint']);
    $body = $r['message']['body'];
    if (array_key_exists('user_token', $body)) echo $body['user_token'];
    else abort(404, 'No user token provided from Musixmatch');
})->purpose('Generates Musixmatch token');
