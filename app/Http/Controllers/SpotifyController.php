<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Validation\ValidationException;
use JsonException;

class SpotifyController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::retry(3, 100)->timeout(25000)
				->get('https://lyrics.paxsenix.org/spotify/search',	['q' => $req['query']]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error('Spotify API error: ', $r);
				return to_route('spotify.index')->withInput()
					->withError('Oops, something went wrong with Spotify API. Please try again later.');
			}
			return view('spotify.result', ['data' => $r]);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('spotify.index')->withInput()
				->withError('Spotify connection error ' . $th->getCode() . ': ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('spotify.index')->withInput()->withErrors($e->errors());
		} catch (RequestException $e) {
			Log::error($e);
			return to_route('spotify.index')->withInput()
				->withError('Spotify HTTP Error ' . $e->response->status());
		} catch (JsonException $e) {
			Log::error($e);
			return to_route('spotify.index')->withInput()
				->withError('Error parsing response: ' . $e->getMessage());
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)
				->get('https://lyrics.paxsenix.org/spotify/lyrics', ['id' => $id]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (is_array($r)) {
				if (array_key_exists('isError', $r) && $r['isError'] === true) {
					abort_if($r['error'] === 'No lyrics found', 404, 'No lyric available for this song');
					Log::error('Spotify API error: ', $r);
					abort(500, $r['error']);
				} else {
					Log::error('Malformed lyric content: ', $r);
					abort(500, 'Malformed lyric content, please contact site owner.');
				}
			} else if (empty($r)) abort(404, 'No lyric available for this song');
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Spotify connection error ' . $th->getCode() . ': ' . $th->getMessage());
		} catch (RequestException $e) {
			Log::error($e);
			abort(
				$e->response->status(),
				$e->response->status() === 404 ? 'No lyric available for this song' : 'Spotify API error ' . $e->response->status()
			);
		} catch (JsonException $e) {
			Log::error($e);
			abort(500, 'Error parsing response: ' . $e->getMessage());
		}
	}
}
