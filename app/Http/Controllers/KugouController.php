<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class KugouController extends Controller
{
	public static string $lrcUrl = 'https://lyrics.kugou.com/';
	public function search(Request $req)
	{
		try {
			$req->validate(['query' => 'required', 'page' => 'nullable|integer|min:1']);
			$response = Http::get('http://mobilecdn.kugou.com/api/v3/search/song', [
				'format' => 'json',
				'keyword' => $req['query'],
				'page' => $req['page'] ?? 1,
				"pagesize" => 20,
				'showtype' => 1
			]);
			$r = self::decodeJson($response);
			if ($r === false) {
				return to_route('kugou.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
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
		try {
			$response = Http::get(
				self::$lrcUrl . 'search',
				['ver' => '1', 'man' => 'yes', 'client' => 'pc', 'hash' => $hash]
			);
			$r = self::decodeJson($response->body());
			abort_if(
				$r === false,
				500,
				'Error parsing JSON response: ' . json_last_error_msg()
			);
			abort_if(
				$r['errcode'] !== 200,
				$r['errcode'],
				'Kugou Music error ' . $r['errcode'] . ': ' . $r['errmsg']
			);
			// Log::warning('Kugou Music error ' . $r['errcode'] . ': ' . $r['errmsg']);
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
				'query' => 'required',
				'minutes' => 'required|numeric|between:0,59',
				'seconds' => 'required|numeric|between:0,59'
			]);
			$response = Http::get(self::$lrcUrl . 'search', [
				'ver' => '1',
				'man' => 'yes',
				'client' => 'pc',
				'keyword' => $req['query'],
				'duration' => ($req['minutes'] * 60 + $req['seconds'])
			]);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('kugou.advanced')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
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
		try {
			$response = Http::get(self::$lrcUrl . 'download', [
				'ver' => '1',
				'id' => $req['id'],
				"client" => 'pc',
				'accesskey' => $req['key'],
				'charset' => 'utf8'
			]);
			$r = self::decodeJson($response->body());
			abort_if(
				$r === false,
				500,
				'Error parsing JSON response: ' . json_last_error_msg()
			);
			// Log::warning('Kugou Music error ' . $r['error_code'] . ': ' . $r['info']);
			abort_if(
				$r['status'] !== 200,
				$r['status'],
				'Kugou Music error ' . $r['error_code'] . ': ' . $r['info']
			);
			if ($r['fmt'] !== 'krc') $context = $r['content'];
			else {
				$krc = base64_decode($r['content']);
				$zip = $this->krchex_xor($krc);
				if (!$zip) abort(500, 'Failed to decrypt KRC data');
				$decoded = zlib_decode($zip);
				if (!$decoded) abort(500, 'Failed to decode KRC data');
				$context = $this->krc2lrc($decoded);
			}
			return response()->json(['format' => $r['fmt'], 'content' => $context]);
		} catch (ConnectionException $e) {
			Log::error($e);
			abort(500, 'Kugou Music connection failed: ' . $e->getMessage());
		}
	}
}
