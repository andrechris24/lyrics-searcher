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
		} catch (ValidationException $e) {
			return to_route('youtube.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('youtube.index')->withInput()->withError($message);
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)
				->get('https://lyrics.paxsenix.org/youtube/lyrics', ['id' => $id]);
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
			} else if (empty($r)) $r = $response->body();
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			Log::error($th);
			$message = self::matchError($th);
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				$message
			);
		}
	}
	private static function matchError(mixed $exception): string
	{
		return match (get_class($exception)) {
			JsonException::class => "Error parsing response: {$exception->getMessage()}",
			ConnectionException::class => "YouTube API connection error {$exception->getCode()}: {$exception->getMessage()}",
			RequestException::class => "YouTube API HTTP Error {$exception->response->status()}",
			default => "YouTube API unexpected error: {$exception->getMessage()}"
		};
	}
}
