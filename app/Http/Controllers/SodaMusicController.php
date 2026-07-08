<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;
use JsonException;

class SodaMusicController extends Controller
{
	public const array SODAMUSIC_HEADERS = [
		'Referer' => 'https://api.qishui.com/',
		'User-Agent' => 'LunaPC/2.6.5(197449790)'
	];
	public static string $url = 'https://api.qishui.com/luna/pc/';
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20']
			);
			$response = Http::retry(3, 100)->timeout(25000)->withHeaders(self::SODAMUSIC_HEADERS)
				->get(self::$url . 'search/track', [
					'aid' => 386088,
					'q' => $req['query'],
					'cursor' => $req['offset'] ?? 0,
					'search_method' => 'input'
				]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			$data = $r['result_groups'][0];
			return view('sodamusic.result', compact('data'));
		} catch (ValidationException $e) {
			return to_route('sodamusic.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('sodamusic.index')->withInput()->withError($message);
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)->withHeaders(self::SODAMUSIC_HEADERS)
				->get(self::$url . 'track_v2', ['track_id' => $id, 'media_type' => 'track']);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			abort_if(
				!array_key_exists('lyric', $r),
				404,
				'No lyric available for this song entry'
			);
			abort_if(
				!array_key_exists('content', $r['lyric']),
				404,
				'Empty lyric, download aborted'
			);
			if ($r['lyric']['type'] === 'krc')
				$r['lyric']['content'] = $this->krc2lrc($r['lyric']['content']);
			return response()->json($r['lyric']);
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
			ConnectionException::class => "Soda Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "Soda Music HTTP Error {$ex->response->status()}",
			default => "Soda Music unexpected error: {$ex->getMessage()}"
		};
	}
}
