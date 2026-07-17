<?php

namespace App\Http\Controllers;

use App\KrcDecoder;
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;
use JsonException;

class KugouController extends Controller
{
	private static string $lrcUrl = 'https://lyrics.kugou.com/';
	private static array $query = ['ver' => 1, 'man' => 'yes', 'client' => 'pc'];
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required', 'page' => 'nullable|integer|min:1']);
			$r = Http::retry(3, 100)->timeout(25000)
				->get('http://mobilecdn.kugou.com/api/v3/search/song', [
					'format' => 'json',
					'keyword' => $req['query'],
					'page' => $req['page'] ?? 1,
					'pagesize' => 20,
					'showtype' => 1
				])->json(null, null, JSON_THROW_ON_ERROR);
			if (!in_array($r['errcode'], [0, 200])) {
				Log::error($r);
				return to_route('kugou.index')->withInput()
					->withError("Kugou Music error {$r['errcode']}: {$r['error']}");
			}
			$data = $r['data'];
			return view('kugou.result', compact('data'));
		} catch (ValidationException $e) {
			return to_route('kugou.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			return to_route('kugou.index')->withInput()->withError(self::matchError($th));
		}
	}
	public function lyrics(string $hash)
	{
		$query = self::$query;
		$query['hash'] = $hash;
		try {
			$r = Http::retry(3, 100)->timeout(25000)
				->get(self::$lrcUrl . 'search', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['errcode'] !== 200) {
				Log::error($r);
				abort($r['errcode'], "Kugou Music error {$r['errcode']}: {$r['errmsg']}");
			}
			return response()->json($r['candidates']);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function advanced(Request $req)
	{
		try {
			$req->validate([
				'artist' => 'required',
				'title' => 'required',
				'minutes' => 'required|numeric|between:0,59',
				'seconds' => 'required|numeric|between:0,59'
			]);
			$query = self::$query;
			$query['keyword'] = "{$req['artist']} - {$req['title']}";
			$query['duration'] = ($req['minutes'] * 60 + $req['seconds']) * 1000;
			$r = Http::retry(3, 100)->timeout(25000)
				->get(self::$lrcUrl . 'search', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			if (!in_array($r['errcode'], [0, 200])) {
				Log::error($r);
				return to_route('kugou.advanced')->withInput()
					->withError("Kugou Music error {$r['errcode']}: {$r['error']}");
			}
			$data = $r['candidates'];
			return view('kugou.advanced.result', compact('data'));
		} catch (ValidationException $e) {
			return to_route('kugou.advanced')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | RequestException | \Exception $th) {
			return to_route('kugou.advanced')->withInput()->withError(self::matchError($th));
		}
	}
	public function get(Request $req)
	{
		$req->validate(['id' => 'required|integer', 'key' => 'required']);
		$query = self::$query;
		$query['id'] = $req['id'];
		$query['accesskey'] = $req['key'];
		$query['charset'] = 'utf8';
		try {
			$r = Http::retry(3, 100)->timeout(25000)
				->get(self::$lrcUrl . 'download', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['status'] !== 200) {
				Log::error($r);
				abort($r['status'], "Kugou Music error {$r['error_code']}: {$r['info']}");
			}
			if ($r['fmt'] !== 'krc') $context = $r['content'];
			else {
				$text = KrcDecoder::decode($r['content']);
				$context = parent::krc2lrc($text);
			}
			return response()->json([
				'format' => $r['fmt'],
				'content' => '[id: ' . $req['id'] . "]\n" . $context,
				'raw' => $r['content']
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function aimp(string $hash)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)->withHeaders([
				'User-Agent' => Request::userAgent()
			])->get(parent::$paxsenix_url . 'kugou/lyrics', ['id' => $hash])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['status'] !== 200) {
				Log::error($r);
				abort($r['status'], "Kugou Music error {$r['status']}: {$r['info']}");
			}
			if ($r['fmt'] === 'krc') $lyric = parent::krc2lrc($r['lyrics_text']);
			else $lyric = $r['lyrics_text'];
			return response()->json(['hash' => $hash, 'lyric' => $lyric]);
		} catch (JsonException | RequestException | ConnectionException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		Log::error($ex);
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Kugou Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => $ex->response->status() === 404 ? 'No lyric available for this song' : "Kugou Music HTTP Error {$ex->response->status()}",
			default => "Kugou Music unexpected error: {$ex->getMessage()}"
		};
	}
}
