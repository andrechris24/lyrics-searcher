<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Log, Http};
use Illuminate\Validation\ValidationException;

class AppleController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required']);
			$response = Http::retry(3, 100)->get(
				"https://itunes.apple.com/search",
				['term' => $req['query'], 'entity' => 'song']
			);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('apple.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('apple.result', $r);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('apple.index')->withInput()
				->withError('Apple Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('apple.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id){
		try {
			$response = Http::retry(3, 100)
			->get("https://lyrics.paxsenix.org/apple-music/lyrics",['id' => $id])
			->throw();
			$r = self::decodeJson($response->body());
			abort_if($r===false,500,'Error parsing response: ' . json_last_error_msg());
			return response()->json([
				'id'=>$id,
				'plain'=>$r['plain'],
				'synced'=>$r['lrc'],
				'syllable'=>$r['elrc'],
				'ttml'=>$r['ttmlContent'],
				'type'=>$r['type'],
				'writers'=>implode(', ', $r['metadata']['songwriters']),
				'length'=> gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
		} catch (ConnectionException $e) {
			Log::error($e);
			abort(500,'Apple Music connection failed: '.$e->getMessage());
		}
	}
}
