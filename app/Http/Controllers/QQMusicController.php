<?php

namespace App\Http\Controllers;

// use App\QrcDecrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
// use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QQMusicController extends Controller
{
	public const array QQ_HEADER = [
		"User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0",
		"Accept" => "application/json, text/plain, */*",
		"Accept-Language" => "en-US;q=0.3,en;q=0.2",
		"Sec-Fetch-Dest" => "empty",
		"Sec-Fetch-Mode" => "cors",
		"Sec-Fetch-Site" => "same-origin"
	];
	// private const QQ_KEY = '!@#)(*$%123ZXC!@!@#)(NHL';
	public static string $url = 'https://u.y.qq.com/cgi-bin/musicu.fcg';
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required', 'page' => 'nullable|integer|min:1']);
			$response = Http::connectTimeout(30)->withHeaders(self::QQ_HEADER)
				->post(self::$url, [
					'comm' => ['ct' => 19, 'cv' => 1859, 'uin' => 0],
					'req' => [
						'method' => "DoSearchForQQMusicDesktop",
						"module" => "music.search.SearchCgiService",
						"param" => [
							'grp' => 1,
							'num_per_page' => 20,
							'page_num' => (int)$req['page'] ?? 1,
							'query' => $req['query'],
							'search_type' => 0
						]
					]
				]);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('qqmusic.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			} else if (!in_array($r['code'], [0, 200])) {
				return to_route('qqmusic.index')->withInput()
					->withError('QQ Music error ' . $r['code']);
			} else if (!in_array($r['req']['code'], [0, 200])) {
				return to_route('qqmusic.index')->withInput()
					->withError('QQ Music request error ' . $r['req']['code']);
			} else if (!in_array($r['req']['data']['code'], [0, 200])) {
				return to_route('qqmusic.index')->withInput()
					->withError('QQ Music data error ' . $r['req']['data']['code']);
			}
			return view('qqmusic.result', $r['req']['data']);
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
			$response = Http::connectTimeout(30)
				->withHeaders(["Referer" => "https://y.qq.com/portal/player.html"])
				->get('https://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric_new.fcg', [
					'g_tk' => 5381,
					'format' => 'json',
					'inCharset' => 'utf-8',
					'outCharset' => 'utf-8',
					'songmid' => $id
				]);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			if (!array_key_exists('lyric', $r)) {
				Log::info(
					'No lyric available for songmid {id}, codes:',
					['id' => $id, 'code' => $r]
				);
				abort(404, 'No lyric available for this song');
			}
			$data = ['lyric' => base64_decode($r['lyric']), 'id' => $id];
			return response()->json($data);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'QQ Music connection failed: ' . $th->getMessage());
		}
	}
}
