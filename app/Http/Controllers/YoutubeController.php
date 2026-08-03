<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\{ConnectionException, RequestException};
use JsonException;

class YoutubeController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$r = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . 'youtube/search', ['q' => $req['query']])->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error('YouTube API error: ', $r);
				abort(
					500,
					'Oops, something went wrong while loading results. Please try again later.'
				);
			}
			return response()
				->json(['html' => view('youtube.list', ['data' => $r])->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . 'youtube/lyrics', ['id' => $id]);
			abort_if(
				empty($response->body()) || $response->body() === '""',
				404,
				'No lyric available for this song'
			);
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
			} else if (empty($r) || $r === false) $r = $response->body();
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		if (get_class($ex) === RequestException::class) {
			Log::warning($ex->response->effectiveUri());
			if ($ex->response->status() !== 404) Log::error($ex);
		}
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "YouTube API connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "YouTube API HTTP Error {$ex->response->status()}",
			default => "YouTube API unexpected error: {$ex->getMessage()}"
		};
	}
}
