<?php

namespace App\Http\Controllers;

use App\KrcDecoder;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class KugouController extends Controller
{
	public static string $lrcUrl = 'https://lyrics.kugou.com/';
	public static array $query = ['ver' => 1, 'man' => 'yes', 'client' => 'pc'];
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required', 'page' => 'nullable|integer|min:1']);
			$response = Http::connectTimeout(30)
				->get('http://mobilecdn.kugou.com/api/v3/search/song', [
					'format' => 'json',
					'keyword' => $req['query'],
					'page' => $req['page'] ?? 1,
					'pagesize' => 20,
					'showtype' => 1
				]);
			$r = self::decodeJson($response);
			if ($r === false) {
				return to_route('kugou.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			} else if (!in_array($r['errcode'], [0, 200])) {
				return to_route('kugou.index')->withInput()
					->withError('Kugou Music error ' . $r['errcode'] . ': ' . $r['error']);
			}
			$data = $r['data'];
			return view('kugou.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('kugou.index')->withInput()
				->withError('Kugou Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('kugou.index')->withInput()->withErrors($e->errors());
		}
	}
	public function lyrics(string $hash)
	{
		$query = self::$query;
		$query['hash'] = $hash;
		try {
			$response = Http::connectTimeout(30)->get(self::$lrcUrl . 'search', $query);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			abort_if(
				$r['errcode'] !== 200,
				$r['errcode'],
				'Kugou Music error ' . $r['errcode'] . ': ' . $r['errmsg']
			);
			return response()->json($r['candidates']);
		} catch (ConnectionException $e) {
			Log::error($e);
			abort(500, 'Kugou Music connection failed: ' . $e->getMessage());
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
			$query['keyword'] = $req['artist'] . ' - ' . $req['title'];
			$query['duration'] = ($req['minutes'] * 60 + $req['seconds']) * 1000;
			$response = Http::connectTimeout(30)->get(self::$lrcUrl . 'search', $query);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('kugou.advanced')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			} else if (!in_array($r['errcode'], [0, 200])) {
				return to_route('kugou.advanced')->withInput()
					->withError('Kugou Music error ' . $r['errcode'] . ': ' . $r['error']);
			}
			$data = $r['candidates'];
			return view('kugou.advanced.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('kugou.advanced')->withInput()
				->withError('Kugou Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('kugou.advanced')->withInput()->withErrors($e->errors());
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
			$response = Http::connectTimeout(30)->get(self::$lrcUrl . 'download', $query);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			abort_if(
				$r['status'] !== 200,
				$r['status'],
				'Kugou Music error ' . $r['error_code'] . ': ' . $r['info']
			);
			if ($r['fmt'] !== 'krc') $context = $r['content'];
			else {
				$text = KrcDecoder::decode($r['content']);
				$context = $this->krc2lrc($text);
			}
			return response()->json([
				'format' => $r['fmt'],
				'content' => '[id: ' . $req['id'] . "]\n" . $context,
				'raw' => $r['content']
			]);
		} catch (ConnectionException $e) {
			Log::error($e);
			abort(500, 'Kugou Music connection failed: ' . $e->getMessage());
		}
	}
}
