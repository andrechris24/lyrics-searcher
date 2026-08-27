<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Http};
use JsonException;

class AmazonController extends Controller
{
	public function search(Request $req)
	{
		abort_if(empty(env('PAXSENIX_TOKEN')), 500, 'Paxsenix API token is required');
		try {
			$req->validate(['query' => 'required']);
			$r = Http::retry(3, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get("https://api.paxsenix.org/amazon-music/search", ['q' => $req['query']])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Amazon Music API error: ', $r);
				abort(500, 'Oops, something went wrong with Amazon Music API. Please try again later.');
			} else if (array_key_exists('error', $r['results'])) {
				Log::error($r['results']);
				abort(500, 'Oops, an error occurred while loading results');
			}
			return response()->json(['html' => view('amazon.list', ['data' => $r['results']])
				->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	public function get(string $id)
	{
		abort_if(empty(env('PAXSENIX_TOKEN')), 500, 'Paxsenix API token is required');
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get("https://api.paxsenix.org/lyrics/amazonmusic", ['id' => $id])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error("Amazon Music API error: {$r['message']}", $r);
				abort(500, 'Oops, an error occurred with Amazon Music API.');
			}
			if (!empty($r['json'])) Log::debug($r['json']);
			abort_if(empty($r['text']) && empty($r['lrc']), 404, 'No lyric available for this song');
			return response()->json([
				'id' => $id,
				'plain' => $r['text'],
				'synced' => $r['lrc'],
				'syllable' => $r['json']
				// 'credits' => $r['credits_line'],
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	public function download(Request $req)
	{
		abort_if(empty(env('PAXSENIX_TOKEN')), 500, 'Paxsenix API token is required');
		$req->validate(['url' => 'required|url']);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get("https://api.paxsenix.org/dl/amazonmusic", ['url' => $req['url']])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error("Amazon Music API error: {$r['message']}", $r);
				abort(500, 'Oops, an error occurred with Amazon Music API.');
			}
			// Log::debug($r);
			return response()->json($r);
		} catch (ConnectionException | RequestException | JsonException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				parent::lyricallyError($e)
			);
		}
	}
}
