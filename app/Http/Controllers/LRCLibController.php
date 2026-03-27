<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class LRCLibController extends Controller
{
	public function standard(Request $request)
	{
		try {
			$request->validate(['query' => 'required']);
			$response = Http::get('https://lrclib.net/api/search', [
				'q' => $request['query']
			]);
			$data = json_decode($response->body());
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('lrclib.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
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
			$q = [
				'track_name' => $request['title'],
				'artist_name' => $request['artist'],
				'album_name' => $request['album']
			];
			$response = Http::get('https://lrclib.net/api/search', $q);
			$data = json_decode($response->body());
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('lrclib.advanced')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
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
