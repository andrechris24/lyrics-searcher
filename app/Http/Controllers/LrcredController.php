<?php

namespace App\Http\Controllers;

use App\Http\Requests\{AdvancedSearchRequest, BasicSearchRequest};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\{ConnectionException, RequestException};
use JsonException;

class LrcredController extends Controller
{
	public static string $biniUrl = 'https://lyrics-api.binimum.org/';
	public static string $lrcUrl = 'https://lrc.red/s/';
	public function standard(BasicSearchRequest $req)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)->get(self::$biniUrl, ['q' => $req['query']])
				->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('lrcred.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Error loading results: ' . self::matchError($e)
			);
		}
	}
	public function advanced(AdvancedSearchRequest $req)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)->get(
				self::$biniUrl,
				['track' => $req['title'], 'artist' => $req['artist'], 'album' => $req['album']]
			)->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('lrcred.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Error loading results: ' . self::matchError($e)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		Log::error($ex);
		if (get_class($ex) === RequestException::class) {
			$reqerr = match ($ex->response->status()) {
				400 => 'Invalid input, please make sure all * fields filled.',
				404 => 'No lyric found for this result',
				default => "lrc.red HTTP Error {$ex->response->status()}"
			};
		}
		return match (get_class($ex)) {
			JsonException::class => "Malformed response ({$ex->getMessage()})",
			ConnectionException::class => "lrc.red connection error, {$ex->getMessage()}",
			RequestException::class => $reqerr ?? "lrc.red API Error {$ex->response->status()}",
			default => "lrc.red unexpected error"
		};
	}
}
