<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;
use JsonException;

class NetEaseController extends Controller
{
	public const array NETEASE_HEADERS =
	['Referer' => 'https://music.163.com', 'X-Real-IP' => '202.96.0.0'];
	public static string $url = 'https://music.163.com/api/';
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20']
			);
			$response = Http::retry(3, 100)->timeout(25000)->withHeaders(self::NETEASE_HEADERS)
				->get(self::$url . 'search/get', [
					's' => $req['query'],
					'type' => 1,
					'limit' => 20, //Match result count as LRCLib
					'offset' => $req['offset'] ?? 0
				]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['code'] !== 200) {
				Log::error($r);
				return to_route('netease.index')->withInput()
					->withError("NetEase Music HTTP Error {$r['code']}");
			}
			$data = $r['result'];
			return view('netease.result', compact('data'));
		} catch (ValidationException $e) {
			return to_route('netease.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('netease.index')->withInput()->withError($message);
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)->withHeaders(self::NETEASE_HEADERS)
				->get(
					self::$url . 'song/lyric',
					['kv' => -1, 'lv' => -1, 'os' => 'pc', 'id' => $id]
				);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['code'] !== 200) {
				Log::error($r);
				abort($r['code'], "NetEase Music HTTP Error {$r['code']}");
			}
			abort_if(
				array_key_exists('needDesc', $r),
				404,
				'No lyric available for this song entry'
			);
			return response()->json($r);
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
			ConnectionException::class => "NetEase Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "NetEase Music HTTP Error {$ex->response->status()}",
			default => "NetEase Music unexpected error: {$ex->getMessage()}"
		};
	}
}
