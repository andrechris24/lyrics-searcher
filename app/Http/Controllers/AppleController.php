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
		$req->validate(['query' => 'required', 'api' => 'required|in:itunes,paxsenix']);
		try {
			if ($req['api'] === 'paxsenix') {
				abort_if(
					empty(env('PAXSENIX_TOKEN')),
					401,
					'API Token is required for Paxsenix request and song downloads'
				);
				$r = Http::retry(2, 100)->timeout(25000)
					->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
					->get(parent::$paxsenix_url . "apple-music/search", ['q' => $req['query']])
					->json(null, null, JSON_THROW_ON_ERROR);
			} else {
				$r = Http::retry(3, 100)->timeout(25000)->get(
					"https://itunes.apple.com/search",
					['term' => $req['query'], 'entity' => 'song']
				)->json(null, null, JSON_THROW_ON_ERROR);
			}
			return response()->json(['html' => view('apple.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort_if(
				$req['api'] === 'paxsenix',
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				'Error loading results: ' . parent::lyricallyError($th)
			);
			Log::error($th);
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				'Error loading results: ' . match (get_class($th)) {
					JsonException::class => "Malformed response ({$th->getMessage()})",
					ConnectionException::class => "Apple Music connection error, {$th->getMessage()}",
					RequestException::class => "Apple Music HTTP Error {$th->response->status()}",
					default => "Apple Music unexpected error"
				}
			);
		}
	}
	public function get(int $id)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->get(parent::$lyrically_url . "apple-music/lyrics", ['id' => $id])
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
				'Error retrieving lyric: ' . parent::lyricallyError($th, true)
			);
		}
	}
	public function download(Request $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API Token is required for Paxsenix request and song downloads'
		);
		$req->validate(['url' => 'required|url']);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get(parent::$paxsenix_url . 'dl/applemusic', ['url' => $req['url']])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Apple Music API error: ', $r);
				abort(
					500,
					'Oops, something went wrong while downloading song. Please try again later.'
				);
			}
			// Log::debug($r);
			return response()->json($r);
		} catch (ConnectionException | RequestException | JsonException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Download failed: ' . parent::lyricallyError($e)
			);
		}
	}
	private static function mlWorkaround(string|null $elrc, int $last): string|null
	{
		if (empty($elrc)) return null;
		return env('MINILYRICS_COMPATIBLE', false) ?
			sprintf(
				"%s \n[%s]",
				Str::replace(">\n", "> \n", $elrc, false),
				parent::formatTime($last + 1, true)
			) : $elrc;
	}
}
