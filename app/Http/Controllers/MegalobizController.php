<?php

namespace App\Http\Controllers;

use App\Http\Requests\{AdvancedAltRequest, BasicSearchRequest};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\{ConnectionException, RequestException};
use JsonException;

class MegalobizController extends Controller
{
	public static string $url = 'https://api.megalobiz.com/api/public/v1/';
	public function standard(BasicSearchRequest $req)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['X-API-KEY' => env('MEGALOBIZ_KEY')])
				->get(self::$url . 'search', ['q' => $req['query'], 'limit' => 20])
				->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('megalobiz.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Error loading results: ' . self::matchError($e)
			);
		}
	}
	public function advanced(AdvancedAltRequest $req)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['X-API-KEY' => env('MEGALOBIZ_KEY')])->get(self::$url . 'search',[
					'title' => $req['title'],
					'artist' => $req['artist'],
					'duration'=> $req['minutes']*60+$req['seconds'],
					'limit' => 20
				])->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('megalobiz.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Error loading results: ' . self::matchError($e)
			);
		}
	}
	public function get(int $id)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['X-API-KEY' => env('MEGALOBIZ_KEY')])->get(self::$url . 'lrc/' . $id)
				->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json($r);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Error retrieving lyric: ' . self::matchError($e)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		Log::error($ex);
		if (get_class($ex) === RequestException::class) {
			$reqerr = match ($ex->response->status()) {
				400 => 'Invalid input, please make sure all * fields filled.',
				401 => 'The provided Megalobiz API key was invalid or revoked, please contact site owner.',
				404 => 'No lyric found for this result',
				429 => empty(env('MEGALOBIZ_KEY')) ? 'You\'ve exceeded Megalobiz rate limit without API Key. Wait for a while or change your IP.' : 'Megalobiz rate limit was exceeded',
				default => "Megalobiz HTTP Error {$ex->response->status()}"
			};
		}
		return match (get_class($ex)) {
			JsonException::class => "Malformed response ({$ex->getMessage()})",
			ConnectionException::class => "Megalobiz connection error, {$ex->getMessage()}",
			RequestException::class => $reqerr ?? "Megalobiz API Error {$ex->response->status()}",
			default => "Megalobiz unexpected error"
		};
	}
}
