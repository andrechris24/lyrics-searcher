<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class KugouController extends Controller
{
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
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
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
				'https://lyrics.kugou.com/search',
				['ver' => '1', 'man' => 'yes', "client" => 'pc', 'hash' => $hash]
			);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return response()->json([
					'message' => 'Error parsing JSON response: ' . json_last_error_msg()
				], 500);
			} else if ($r['errcode'] !== 200) {
				Log::warning('Kugou Music error ' . $r['errcode'] . ': ' . $r['errmsg']);
				return response()->json([
					'message' => 'Kugou Music error ' . $r['errcode'] . ': ' . $r['errmsg']
				], $r['errcode']);
			}
			$data = $r['candidates'];
			return response()->json($r['candidates']);
		} catch (ConnectionException $e) {
			Log::error($e);
			return response()->json([
				'message' => 'Kugou Music connection failed: ' . $e->getMessage()
			], 500);
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
			$response = Http::get('https://lyrics.kugou.com/search', [
				'ver' => '1',
				'man' => 'yes',
				"client" => 'pc',
				'keyword' => $req['query'],
				'duration' => ($req['minutes'] * 60 + $req['seconds'])
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
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
			$response = Http::get('https://lyrics.kugou.com/download', [
				'ver' => '1',
				'id' => $req['id'],
				"client" => 'pc',
				'accesskey' => $req['key'],
				'charset' => 'utf8'
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return response()->json([
					'message' => 'Error parsing JSON response: ' . json_last_error_msg()
				], 500);
			} else if ($r['status'] !== 200) {
				return response()->json([
					'Kugou Music error ' . $r['error_code'] . ': ' . $r['info']
				], $r['status']);
			}
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
			return back()->withError('Kugou Music connection failed: ' . $e->getMessage());
		}
	}
}
