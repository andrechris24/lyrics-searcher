<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;

class SodaMusicController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate(
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20'],
				['integer' => 'The offset number is malformed.']
			);
			$response = Http::withHeaders([
				"Referer" => "https://api.qishui.com/", 
				'User-Agent' => 'LunaPC/2.6.5(197449790)'
			])->get('https://api.qishui.com/luna/pc/search/track', [
				'aid'=>386088,
				'q' => $req['query'],
				'cursor'=>$req['offset']??0,
				'search_method' => 'input'
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('sodamusic.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$data = $r['result_groups'][0];
			// dd($data);
			return view('sodamusic.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('sodamusic.index')->withInput()
				->withError('Soda Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('sodamusic.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::withHeaders([
				"Referer" => "https://api.qishui.com/", 
				'User-Agent' => 'LunaPC/2.6.5(197449790)'
			])->get(
				'https://api.qishui.com/luna/pc/track_v2',
				["track_id"=> $id, "media_type"=> "track"]
			);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return response()->json([
					'message' => 'Error parsing JSON response: ' . json_last_error_msg()
				], 500);
			}
			if(!array_key_exists('lyric', $r))
				return response()->json(['message'=>'No lyric available for this song'],404);
			if($r['lyric']['type']==='krc')
				$r['lyric']['content']=$this->krc2lrc($r['lyric']['content']);
			return response()->json($r);
		} catch (ConnectionException $th) {
			Log::error($th);
			return response()->json([
				'message' => 'Soda Music connection failed: ' . $th->getMessage()
			], 500);
		}
	}
}
