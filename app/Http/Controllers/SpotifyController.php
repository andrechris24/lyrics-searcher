<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\ValidationException;

class SpotifyController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::retry(3, 100)->get(
				'https://lyrics.paxsenix.org/spotify/search',
				['q' => $req['query']]
			)->throw();
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('spotify.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('spotify.result', ['data' => $r]);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('spotify.index')->withInput()
				->withError('Spotify connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('spotify.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(3, 100)
				->get('https://lyrics.paxsenix.org/spotify/lyrics', ['id' => $id])->throw();
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			if (is_array($r)) {
				if (array_key_exists('isError', $r) && $r['isError'] === true) {
					abort_if($r['error']==='No lyrics found',404,'No lyric available for this song');
					Log::error($r);
					abort(500, $r['error']);
				} else {
					Log::error('Malformed lyric content: ', $r);
					abort(500, 'Malformed lyric content, please contact site owner.');
				}
			} else if (empty($r)) abort(404, 'No lyric available for this song');
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Spotify connection failed: ' . $th->getMessage());
		}
	}
}
