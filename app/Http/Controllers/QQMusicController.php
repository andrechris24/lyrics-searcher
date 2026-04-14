<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class QQMusicController extends Controller
{
	public const array QQ_HEADER = ["Referer" => "https://y.qq.com/portal/player.html"];
	public static string $url = 'https://c.y.qq.com/';
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::withHeaders(self::QQ_HEADER)->get(
				self::$url . 'splcloud/fcgi-bin/smartbox_new.fcg',
				['inCharset' => 'utf-8', 'outCharset' => 'utf-8', 'key' => $req['query']]
			);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('qqmusic.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$data = $r['data']['song'];
			return view('qqmusic.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('qqmusic.index')->withInput()
				->withError('QQ Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('qqmusic.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::withHeaders(self::QQ_HEADER)
				->get(self::$url . 'lyric/fcgi-bin/fcg_query_lyric_new.fcg', [
					'g_tk' => '5381',
					'format' => 'json',
					'inCharset' => 'utf-8',
					'outCharset' => 'utf-8',
					'songmid' => $id
				]);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing JSON response: ' . json_last_error_msg());
			if (!array_key_exists('lyric', $r)) {
				Log::info(
					'No lyric available for songmid {id}, response code: {code}',
					['id' => $id, 'code' => json_encode($r)]
				);
				abort(404, 'No lyric available for this song');
			}
			$data = ['lyric' => base64_decode($r['lyric'])];
			return response()->json($data);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'QQ Music connection failed: ' . $th->getMessage());
		}
	}
}
