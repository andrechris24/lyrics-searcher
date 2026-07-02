<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Validation\ValidationException;
use JsonException;

class YoutubeController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::retry(3, 100)->timeout(25000)
				->get('https://lyrics.paxsenix.org/youtube/search', ['q' => $req['query']]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error('YouTube API error: ', $r);
				return to_route('youtube.index')->withInput()
					->withError('Oops, something went wrong with YouTube API. Please try again later.');
			}
			return view('youtube.result', ['data' => $r]);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('youtube.index')->withInput()
				->withError('YouTube connection error ' . $th->getCode() . ': ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('youtube.index')->withInput()->withErrors($e->errors());
		} catch (RequestException $e) {
			Log::error($e);
			return to_route('youtube.index')->withInput()
				->withError('YouTube HTTP Error ' . $e->response->status());
		} catch (JsonException $e) {
			Log::error($e);
			return to_route('youtube.index')->withInput()
				->withError('Error parsing response: ' . $e->getMessage());
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)
				->get('https://lyrics.paxsenix.org/youtube/lyrics', ['id' => $id]);
			abort_if(empty($response->body()), 404, 'No lyric available for this song');
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (is_array($r)) {
				if (array_key_exists('isError', $r) && $r['isError'] === true) {
					abort_if($r['error'] === 'No lyrics found', 404, 'No lyric available for this song');
					Log::error('YouTube API error: ', $r);
					abort(500, $r['error']);
				} else {
					Log::error('Malformed lyric content: ', $r);
					abort(500, 'Malformed lyric content, please contact site owner.');
				}
			} else if (empty($r)) $r = $response->body();
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'YouTube connection error ' . $th->getCode() . ': ' . $th->getMessage());
		} catch (RequestException $e) {
			Log::error($e);
			abort(
				$e->response->status(),
				$e->response->status() === 404 ? 'No lyric available for this song' : 'YouTube API error ' . $e->response->status()
			);
		} catch (JsonException $e) {
			Log::error($e);
			abort(500, 'Error parsing response: ' . $e->getMessage());
		}
	}
}
