<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\ValidationException;

class YoutubeController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::retry(3, 100)
				->get('https://lyrics.paxsenix.org/youtube/search', ['q' => $req['query']])
				->throw();
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('youtube.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('youtube.result', ['data' => $r]);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('youtube.index')->withInput()
				->withError('YouTube connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('youtube.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(3, 100)
				->get('https://lyrics.paxsenix.org/youtube/lyrics', ['id' => $id])->throw();
			abort_if(empty($response->body()), 404, 'No lyric available for this song');
			if (json_validate($response->body())) {
				$r = json_decode($response->body(), true);
				if (is_array($r)) {
					if (array_key_exists('isError', $r) && $r['isError'] === true) {
						Log::error($r);
						abort(404, $r['error']);
					} else {
						Log::error('Malformed lyric content: ', $r);
						abort(500, 'Malformed lyric content, please report to developer.');
					}
				}
			} else $r = $response->body();
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'YouTube connection failed: ' . $th->getMessage());
		}
	}
}
