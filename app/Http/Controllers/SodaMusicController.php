<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class SodaMusicController extends Controller
{
	public const array SODAMUSIC_HEADERS = [
		"Referer" => "https://api.qishui.com/",
		'User-Agent' => 'LunaPC/2.6.5(197449790)'
	];
	public static string $url = 'https://api.qishui.com/luna/pc/';
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20']
			);
			$response = Http::connectTimeout(30)->withHeaders(self::SODAMUSIC_HEADERS)
				->get(self::$url . 'search/track', [
					'aid' => 386088,
					'q' => $req['query'],
					'cursor' => $req['offset'] ?? 0,
					'search_method' => 'input'
				]);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('sodamusic.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			$data = $r['result_groups'][0];
			return view('sodamusic.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('sodamusic.index')->withInput()
				->withError('Soda Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('sodamusic.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::connectTimeout(30)->withHeaders(self::SODAMUSIC_HEADERS)->get(
				self::$url . 'track_v2',
				["track_id" => $id, "media_type" => "track"]
			);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			abort_if(
				!array_key_exists('lyric', $r),
				404,
				'No lyric available for this song'
			);
			if ($r['lyric']['type'] === 'krc')
				$r['lyric']['content'] = $this->krc2lrc($r['lyric']['content']);
			return response()->json($r);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Soda Music connection failed: ' . $th->getMessage());
		}
	}
}
