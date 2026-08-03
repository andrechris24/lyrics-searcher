<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Http};
use Illuminate\Support\Str;
use JsonException;

class AppleController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$r = Http::retry(3, 100)->timeout(25000)->get(
				"https://itunes.apple.com/search",
				['term' => $req['query'], 'entity' => 'song']
			)->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('apple.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function get(int $id)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . "apple-music/lyrics", ['id' => $id])
				->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error("Apple Music API error: {$r['message']}", $r);
				abort(500, 'Oops, an error occurred with Apple Music API.');
			}
			return response()->json([
				'id' => $id,
				'plain' => $r['plain'],
				'synced' => $r['lrc'],
				'syllable' => env('MINILYRICS_COMPATIBLE') ? Str::replace(">\n", "> \n", $r['elrc'], false) : $r['elrc'],
				'multisyl' => env('MINILYRICS_COMPATIBLE') ? Str::replace(">\n", "> \n", $r['elrcMultiPerson'], false) : $r['elrcMultiPerson'],
				'ttml' => $r['ttmlContent'],
				'type' => $r['type'],
				'writers' => implode(', ', $r['metadata']['songwriters']),
				'length' => gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
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
			ConnectionException::class => "Apple Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => $ex->response->status() === 404 ? 'No result' : "Apple Music HTTP Error {$ex->response->status()}",
			default => "Apple Music unexpected error: {$ex->getMessage()}"
		};
	}
}
