<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class QQMusicController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::withHeaders([
				"Referer" => "http://y.qq.com/portal/player.html"
			])->get(
				'https://c.y.qq.com/splcloud/fcgi-bin/smartbox_new.fcg',
				['inCharset' => 'utf-8', 'outCharset' => 'utf-8', 'key' => $req['query']]
			);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('qqmusic.index')
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$data = $r['data']['song'];
			return view('qqmusic.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('qqmusic.index')
				->withError('QQ Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('qqmusic.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::withHeaders([
				"Referer" => "http://y.qq.com/portal/player.html"
			])->get('http://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric_new.fcg', [
				'g_tk' => '5381',
				'format' => 'json',
				'inCharset' => 'utf-8',
				'outCharset' => 'utf-8',
				'songmid' => $id
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return response()->json([
					'message' => 'Error parsing JSON response: ' . json_last_error_msg()
				], 500);
			} else if (!array_key_exists('lyric', $r))
				return response()->json(['message' => 'No lyric available for this song'], 404);
			$data = ['lyric' => base64_decode($r['lyric'])];
			return response()->json($data);
		} catch (ConnectionException $th) {
			Log::error($th);
			return response()->json([
				'message' => 'QQ Music connection failed: ' . $th->getMessage()
			], 500);
		}
	}
}
