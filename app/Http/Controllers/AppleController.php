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
			$message = match (get_class($th)) {
				JsonException::class => 'Error parsing response: ' . $th->getMessage(),
				ConnectionException::class => 'Apple Music connection error ' . $th->getCode() . ': ' . $th->getMessage(),
				RequestException::class => 'Apple Music HTTP Error ' . $th->response->status(),
				default => 'Apple Music unexpected error : ' . $th->getMessage()
			};
			return to_route('apple.index')->withInput()->withError($message);
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
				'syllable' => Str::replace(">\n", "> \n", $r['elrc'], false), //Counter MiniLyrics bug
				'ttml' => $r['ttmlContent'],
				'type' => $r['type'],
				'writers' => implode(', ', $r['metadata']['songwriters']),
				'length' => gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			Log::error($th);
			$message = match (get_class($th)) {
				JsonException::class => 'Error parsing response: ' . $th->getMessage(),
				ConnectionException::class => 'Apple Music connection error ' . $th->getCode() . ': ' . $th->getMessage(),
				RequestException::class => $th->response->status() === 404 ? 'No lyric available for this song' : 'Apple Music error ' . $th->response->status(),
				default => 'Apple Music unexpected error : ' . $th->getMessage()
			};
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				$message
			);
		}
	}
}
