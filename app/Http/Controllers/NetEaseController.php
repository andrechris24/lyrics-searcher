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
				['query' => 'required', 'offset' => 'nullable|integer|min:0|multiple_of:20'],
				['integer' => 'The offset number is malformed.']
			);
			$response = Http::withHeaders(
				["Referer" => "https://music.163.com", 'X-Real-IP' => '202.96.0.0']
			)->get('https://music.163.com/api/search/get', [
				's' => $req['query'],
				'type' => '1',
				'limit' => 20, //Match result count as LRCLib
				'offset' => $req['offset'] ?? 0
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('netease.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			} else if ($r['code'] !== 200) {
				return to_route('netease.index')->withInput()
					->withError("NetEase Music HTTP Error " . $r['code']);
			}
			$data = $r['result'];
			return view('netease.result', compact('data'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('netease.index')->withInput()
				->withError('NetEase Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
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
			}else if(array_key_exists('needDesc', $r) && $r['needDesc']===true){
				return response()->json(['message'=>'No lyric available for this song'],404);
			}
			return response()->json($r);
		} catch (ConnectionException $th) {
			Log::error($th);
			return response()->json([
				'message' => 'NetEase Music connection failed: ' . $th->getMessage()
			], 500);
		}
	}
	private function parseKLyric($lyricText)
	{
		$enhancedlyricText = "";
		$metaRegex = "/^\[(\S+):(\S+)\]$/";
		$timestampsRegex = "/^\[(\d+),(\d+)\]/";
		$timestamps2Regex = "/\((\d+),(\d+)\)([^\(]*)/";
		$lines = preg_split("/[\r\n]/", $lyricText);
		foreach ($lines as $line) {
			if (preg_match($metaRegex, $line, $matches)) // meta info
				$enhancedlyricText .= $matches[0] . "\r\n";
			else if (preg_match($timestampsRegex,$line,$matches)) {
				$lyricLine = "";
				$startTime = (int)$matches[1];
				$duration = (int)$matches[2];
				$lyricLine = "[" . $this->formatTime($startTime/1000) . "]";
				// parse sub-timestamps
				$subStartTime = $startTime;
				if (preg_match_all($timestamps2Regex, $line, $subMatches)) {
					for ($a = 0; $a < count($subMatches[0]); $a++) {
						$subDuration = (int)$subMatches[2][$a];
						$subWord = $subMatches[3][$a];
						$lyricLine .= "<" . $this->formatTime($subStartTime/1000) . ">" . $subWord;
						$subStartTime += $subDuration;
					}
				}
				$lyricLine .= "<" . $this->formatTime(($startTime + $duration)/1000) . "> ";
				$enhancedlyricText .= $lyricLine . "\r\n";
			}
		}
		return $enhancedlyricText;
	}
}
