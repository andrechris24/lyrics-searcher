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
			if ($r['type'] === 'Syllable')
				$endtime = $r['content'][count($r['content']) - 1]['sectionEnd'];
			return response()->json([
				'id' => $id,
				'plain' => $r['plain'],
				'synced' => $r['lrc'],
				'syllable' => self::mlWorkaround($r['elrc'], $endtime ?? 0),
				'multisyl' => self::mlWorkaround($r['elrcMultiPerson'], $endtime ?? 0),
				'ttml' => $r['ttmlContent'],
				'type' => $r['type'],
				'writers' => implode(', ', $r['metadata']['songwriters']),
				'length' => gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		if (get_class($ex) === RequestException::class) {
			Log::warning('Request failed for ' . $ex->response->effectiveUri());
			if ($ex->response->status() !== 404) Log::error($ex);
		}
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Apple Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "Apple Music HTTP Error {$ex->response->status()}",
			default => "Apple Music unexpected error: {$ex->getMessage()}"
		};
	}
	private static function mlWorkaround(string|null $elrc, int $last): string|null
	{
		if (empty($elrc)) return null;
		return env('MINILYRICS_COMPATIBLE') ?
			sprintf(
				"%s\n[%s]",
				Str::replace(">\n", "> \n", $elrc, false),
				parent::formatTime($last / 1000)
			) : $elrc;
	}
}
