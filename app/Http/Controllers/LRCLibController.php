<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class LRCLibController extends Controller
{
	public static string $url = 'https://lrclib.net/api/search';
	public function standard(Request $request)
	{
		try {
			$request->validate(['query' => 'required']);
			$response = Http::get(self::$url, ['q' => $request['query']]);
			$data = self::decodeJson($response->body());
			if ($data === false) {
				return to_route('lrclib.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('lrclib.result', compact('data'));
		} catch (ConnectionException $e) {
			Log::error($e);
			return to_route('lrclib.index')->withInput()
				->withError('LRCLib connection failed: ' . $e->getMessage());
		} catch (ValidationException $e) {
			return to_route('lrclib.index')->withInput()->withErrors($e->errors());
		}
	}
	public function advanced(Request $request)
	{
		try {
			$request->validate(['title' => 'required']);
			$response = Http::get(self::$url, [
				'track_name' => $request['title'],
				'artist_name' => $request['artist'],
				'album_name' => $request['album']
			]);
			$data = self::decodeJson($response->body());
			if ($data === false) {
				return to_route('lrclib.advanced')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('lrclib.advanced.result', compact('data'));
		} catch (ConnectionException $e) {
			Log::error($e);
			return to_route('lrclib.advanced')->withInput()
				->withError('LRCLib connection failed: ' . $e->getMessage());
		} catch (ValidationException $e) {
			return to_route('lrclib.advanced')->withInput()->withErrors($e->errors());
		}
	}
}
