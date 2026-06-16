<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KugouController;
use App\Http\Controllers\LRCLibController;
use App\Http\Controllers\MusixmatchController;
use App\Http\Controllers\NetEaseController;
use App\Http\Controllers\QQMusicController;
use App\Http\Controllers\SodaMusicController;
use App\Http\Controllers\SingleController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\DeezerController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\YoutubeController;

Route::view('/', 'index')->name('home');
Route::get('result', [SingleController::class, 'search'])->name('result');
Route::prefix('lrclib')->name('lrclib.')->group(function () {
	Route::view('/', 'lrclib.index')->name('index');
	Route::view('advanced', 'lrclib.advanced.index')->name('advanced');
	Route::controller(LRCLibController::class)->group(function () {
		Route::name('search')->group(function () {
			Route::get('results', 'standard');
			Route::get('advanced/results', 'advanced')->name('.advanced');
		});
		Route::post('convert', 'convert')->name('convert');
	});
});
Route::prefix('musixmatch')->name('musixmatch.')->group(function () {
	Route::view('/', 'musixmatch.index')->name('index');
	Route::view('advanced', 'musixmatch.advanced.index')->name('advanced');
	Route::controller(MusixmatchController::class)->group(function () {
		Route::get('charts/{type}', 'charts')->name('chart');
		Route::name('search')->group(function () {
			Route::get('results', 'standard');
			Route::get('advanced/results', 'advanced')->name('.advanced');
			Route::get('{id}/{type}', 'get')->name('.get');
		});
	});
});
Route::prefix('netease')->name('netease.')->group(function () {
	Route::view('/', 'netease.index')->name('index');
	Route::controller(NetEaseController::class)->name('search')->group(function () {
		Route::get('results', 'search');
		Route::get('{id}', 'get')->name('.get');
	});
});
Route::prefix('qqmusic')->name('qqmusic.')->group(function () {
	Route::view('/', 'qqmusic.index')->name('index');
	Route::controller(QQMusicController::class)->name('search')->group(function () {
		Route::get('results', 'search');
		Route::get('{id}', 'get')->name('.get');
	});
});
Route::prefix('kugou')->name('kugou.')->group(function () {
	Route::view('/', 'kugou.index')->name('index');
	Route::view('advanced', 'kugou.advanced.index')->name('advanced');
	Route::controller(KugouController::class)->group(function () {
		Route::name('search')->group(function () {
			Route::get('results', 'search');
			Route::get('advanced/results', 'advanced')->name('.advanced');
			Route::get('get', 'get')->name('.get');
		});
		Route::get('{hash}', 'lyrics')->name('lyrics');
	});
});
Route::prefix('sodamusic')->name('sodamusic.')->group(function () {
	Route::view('/', 'sodamusic.index')->name('index');
	Route::controller(SodaMusicController::class)->name('search')->group(function () {
		Route::get('results', 'search');
		Route::get('{id}', 'get')->name('.get');
	});
});
Route::prefix('local')->name('local.')->group(function () {
	Route::view('/', 'local.index')->name('index');
	Route::view('advanced', 'local.advanced.index')->name('advanced');
	Route::controller(LocalController::class)->group(function () {
		Route::get('latest', 'latest')->name('latest');
		Route::middleware(backpack_middleware())->post('upload', 'upload')->name('upload');
		Route::name('search')->group(function () {
			Route::get('results', 'standard');
			Route::get('advanced/results', 'advanced')->name('.advanced');
			Route::get('{id}', 'aimp')->name('.get');
		});
	});
});
Route::prefix('deezer')->name('deezer.')->group(function () {
	Route::view('/', 'deezer.index')->name('index');
	Route::controller(DeezerController::class)->name('search')->group(function () {
		Route::get('results', 'search');
		Route::get('{id}', 'get')->name('.get');
	});
});
Route::prefix('spotify')->name('spotify.')->group(function () {
	Route::view('/', 'spotify.index')->name('index');
	Route::controller(SpotifyController::class)->name('search')->group(function () {
		Route::get('results', 'search');
		Route::get('{id}', 'get')->name('.get');
	});
});
Route::prefix('youtube')->name('youtube.')->group(function () {
	Route::view('/', 'youtube.index')->name('index');
	Route::controller(YoutubeController::class)->name('search')->group(function () {
		Route::get('results', 'search');
		Route::get('{id}', 'get')->name('.get');
	});
});
Route::view('laravel', 'welcome')->name('laravel');
Route::get('phpinfo', function () {
	return phpinfo();
})->name('php');
