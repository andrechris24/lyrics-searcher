<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Http, Log};
use JsonException;

class SodaMusicController extends Controller
{
	private const array SODAMUSIC_HEADERS = [
		'Referer' => 'https://api.qishui.com/',
		'User-Agent' => 'LunaPC/2.6.5(197449790)'
	];
	private static string $url = 'https://api.qishui.com/luna/pc/';
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20']
			);
			$r = Http::retry(3, 100)->timeout(25000)->withHeaders(self::SODAMUSIC_HEADERS)
				->get(self::$url . 'search/track', [
					'aid' => 386088,
					'q' => $req['query'],
					'cursor' => $req['offset'] ?? 0,
					'search_method' => 'input'
				])->json(null, null, JSON_THROW_ON_ERROR);
			$data = $r['result_groups'][0];
			return response()->json(
				['html' => view('sodamusic.list', compact('data'))->render(), 'data' => $data]
			);
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
			$r = Http::retry(3, 100)->timeout(25000)->withHeaders(self::SODAMUSIC_HEADERS)
				->get(self::$url . 'track_v2', ['track_id' => $id, 'media_type' => 'track'])
				->json(null, null, JSON_THROW_ON_ERROR);
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
				$r['lyric']['content'] = parent::krc2lrc($r['lyric']['content']);
			return response()->json($r['lyric']);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		if (get_class($ex) === RequestException::class && $ex->response->status() !== 404)
			Log::error($ex);
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Soda Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "Soda Music HTTP Error {$ex->response->status()}",
			default => "Soda Music unexpected error: {$ex->getMessage()}"
		};
	}
}
