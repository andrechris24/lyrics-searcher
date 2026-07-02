<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Http};
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
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('apple.index')->withInput()->withError(
				'Apple Music connection error ' . $th->getCode() . ': ' . $th->getMessage()
			);
		} catch (ValidationException $e) {
			return to_route('apple.index')->withInput()->withErrors($e->errors());
		} catch (RequestException $e) {
			Log::error($e);
			return to_route('apple.index')->withInput()
				->withError('Apple Music HTTP Error ' . $e->response->status());
		} catch (JsonException $e) {
			Log::error($e);
			return to_route('apple.index')->withInput()
				->withError('Error parsing response: ' . $e->getMessage());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)
				->get("https://lyrics.paxsenix.org/apple-music/lyrics", ['id' => $id]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error('Apple Music API error: ' . $r['message'], $r);
				abort(500, 'Oops, an error occurred with Apple Music API.');
			}
			return response()->json([
				'id' => $id,
				'plain' => $r['plain'],
				'synced' => $r['lrc'],
				'syllable' => $r['elrc'],
				'ttml' => $r['ttmlContent'],
				'type' => $r['type'],
				'writers' => implode(', ', $r['metadata']['songwriters']),
				'length' => gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
		} catch (ConnectionException $e) {
			Log::error($e);
			abort(
				500, 
				'Apple Music API connection error ' . $e->getCode() . ': ' . $e->getMessage()
			);
		} catch (RequestException $e) {
			Log::error($e);
			abort(
				$e->response->status(),
				$e->response->status() === 404 ? 'No lyric available for this song' : 'Apple Music API error ' . $e->response->status()
			);
		} catch (JsonException $e) {
			Log::error($e);
			abort(500, 'Error parsing response: ' . $e->getMessage());
		}
	}
}
