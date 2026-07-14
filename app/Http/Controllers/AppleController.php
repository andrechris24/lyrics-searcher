<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Http};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;

class AppleController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::retry(3, 100)->timeout(25000)->get(
				"https://itunes.apple.com/search",
				['term' => $req['query'], 'entity' => 'song']
			);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			return view('apple.result', $r);
		} catch (ValidationException $e) {
			return to_route('apple.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('apple.index')->withInput()->withError($message);
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . "apple-music/lyrics", ['id' => $id]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error("Apple Music API error: {$r['message']}", $r);
				abort(500, 'Oops, an error occurred with Apple Music API.');
			}
			return response()->json([
				'id' => $id,
				'plain' => $r['plain'],
				'synced' => $r['lrc'],
				'syllable' => Str::replace(">\n", "> \n", $r['elrc'], false), //Evade MiniLyrics bug
				'multisyl' => Str::replace(">\n", "> \n", $r['elrcMultiPerson'], false),
				'ttml' => $r['ttmlContent'],
				'type' => $r['type'],
				'writers' => implode(', ', $r['metadata']['songwriters']),
				'length' => gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			Log::error($th);
			$message = self::matchError($th);
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				$message
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Apple Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => $ex->response->status() === 404 ? 'No lyric available for this song' : "Apple Music HTTP Error {$ex->response->status()}",
			default => "Apple Music unexpected error: {$ex->getMessage()}"
		};
	}
}
