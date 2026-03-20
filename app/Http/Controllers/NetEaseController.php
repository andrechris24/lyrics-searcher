<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class NetEaseController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required','offset'=>'nullable|integer|min:0|multiple_of:20'],
				['integer'=>'The offset number is malformed.']
			);
			$response = Http::withHeaders(
				["Referer" => "https://music.163.com", 'X-Real-IP' => '202.96.0.0']
			)->get('https://music.163.com/api/search/get',[
				's' => $req['query'], 
				'type' => '1', 
				'limit'=>20,//Match result count as LRCLib
				'offset'=>$req['offset']??0
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('netease.index')
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			} else if ($r['code'] !== 200) {
				return to_route('netease.index')
					->withError("NetEase Music HTTP Error " . $r['code']);
			}
			// dd($r);
			$data = $r['result'];
			return view('netease.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('netease.index')
				->withError('NetEase Music connection failed: ' . $th->getMessage());
		}catch (ValidationException $e) {
			return to_route('netease.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::withHeaders(
				["Referer" => "https://music.163.com", 'X-Real-IP' => '202.96.0.0']
			)->get(
				'https://music.163.com/api/song/lyric',
				['kv' => '-1', 'lv' => '-1', 'os' => 'pc', 'id' => $id]
			);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return response()->json([
					'message' => 'Error parsing JSON response: ' . json_last_error_msg()
				], 500);
			} else if ($r['code'] !== 200) {
				return response()->json([
					'message' => 'NetEase Music HTTP Error ' . $r['code']
				], $r['code']);
			}
			return response()->json($r);
		} catch (ConnectionException $th) {
			Log::error($th);
			return response()->json([
				'message' => 'NetEase Music connection failed: ' . $th->getMessage()
			], 500);
		}
	}
}
