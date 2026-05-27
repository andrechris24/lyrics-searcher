<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class NetEaseController extends Controller
{
	public const array NETEASE_HEADERS =
	['Referer' => 'https://music.163.com', 'X-Real-IP' => '202.96.0.0'];
	public static string $url = 'https://music.163.com/api/';
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20']
			);
			$response = Http::connectTimeout(30)->withHeaders(self::NETEASE_HEADERS)
				->get(self::$url . 'search/get', [
					's' => $req['query'],
					'type' => 1,
					'limit' => 20, //Match result count as LRCLib
					'offset' => $req['offset'] ?? 0
				]);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('netease.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			} else if ($r['code'] !== 200) {
				return to_route('netease.index')->withInput()
					->withError("NetEase Music HTTP Error " . $r['code']);
			}
			$data = $r['result'];
			return view('netease.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('netease.index')->withInput()
				->withError('NetEase Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('netease.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::connectTimeout(30)->withHeaders(self::NETEASE_HEADERS)->get(
				self::$url . 'song/lyric',
				['kv' => -1, 'lv' => -1, 'os' => 'pc', 'id' => $id]
			);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			abort_if($r['code'] !== 200, $r['code'], 'NetEase Music HTTP Error ' . $r['code']);
			abort_if(array_key_exists('needDesc', $r), 404, 'No lyric available for this song');
			return response()->json($r);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'NetEase Music connection failed: ' . $th->getMessage());
		}
	}
}
