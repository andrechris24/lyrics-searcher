<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Sleep; //Required as Musixmatch has strict rate limit
use Illuminate\Validation\ValidationException;

class MusixmatchController extends Controller
{
	public function standard(Request $req)
	{
		if (empty(env('MUSIXMATCH_TOKEN')))
			return to_route('index')->withError('Musixmatch token was not found');
		try {
			$req->validate(['query' => 'required', 'page' => 'nullable|integer|min:1'], [
				'integer' => 'The page number is malformed.'
			]);
			Sleep::for(5)->seconds();
			$response = Http::withHeaders([
				"cookie" => "AWSELBCORS=0; AWSELB=0"
			])->get('https://apic-desktop.musixmatch.com/ws/1.1/track.search', [
				'user_language' => 'en',
				'app_id' => 'web-desktop-app-v1.0',
				'q' => $req['query'],
				'usertoken' => env('MUSIXMATCH_TOKEN'),
				'page' => $req['page'] ?? 1,
				'page_size' => 20, //Match LRCLib result count
				'f_has_lyrics' => 1 //Search tracks with lyrics only
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('musixmatch.advanced')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.index')->withInput()
					->withError($this->getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.result', compact('data', 'header'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('musixmatch.index')->withInput()
				->withError('Musixmatch connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('musixmatch.index')->withInput()->withErrors($e->errors());
		}
	}
	public function advanced(Request $req)
	{
		if (empty(env('MUSIXMATCH_TOKEN')))
			return to_route('index')->withError('Musixmatch token was not found');
		try {
			$req->validate([
				'title' => 'nullable|required_without_all:artist,album|string',
				'artist' => 'nullable|required_without_all:title,album|string',
				'album' => 'nullable|required_without_all:title,artist|string',
				'page' => 'nullable|integer|min:1'
			], [
				'integer' => 'The page number is malformed.',
				'min' => 'The page number is out of range.'
			]);
			Sleep::for(5)->seconds();
			$response = Http::withHeaders([
				"cookie" => "AWSELBCORS=0; AWSELB=0"
			])->get('https://apic-desktop.musixmatch.com/ws/1.1/track.search', [
				'user_language' => 'en',
				'app_id' => 'web-desktop-app-v1.0',
				'q_album' => $req['album'],
				'q_artist' => $req['artist'],
				'q_track' => $req['title'],
				'usertoken' => env('MUSIXMATCH_TOKEN'),
				'page' => $req['page'] ?? 1,
				'page_size' => 20,
				'f_has_lyrics' => 1
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('musixmatch.advanced')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.advanced')->withInput()
					->withError($this->getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.advanced.result', compact('data', 'header'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('musixmatch.advanced')->withInput()
				->withError('Musixmatch connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('musixmatch.advanced')->withInput()->withErrors($e->errors());
		}
	}
	public function charts(string $type)
	{
		if (empty(env('MUSIXMATCH_TOKEN')))
			return to_route('index')->withError('Musixmatch token was not found');
		try {

			Sleep::for(5)->seconds();
			$response = Http::withHeaders([
				"cookie" => "AWSELBCORS=0; AWSELB=0"
			])->get('https://apic-desktop.musixmatch.com/ws/1.1/chart.tracks.get', [
				'user_language' => 'en',
				'app_id' => 'web-desktop-app-v1.0',
				'country' => 'id',
				'chart_name' => $type,
				'usertoken' => env('MUSIXMATCH_TOKEN'),
				'f_has_lyrics' => 1
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return to_route('musixmatch.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.index')->withError($this->getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.chart', compact('data', 'header'));
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('musixmatch.index')
				->withError('Musixmatch connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('musixmatch.index')->withErrors($e->errors());
		}
	}
	public function get(int $id, string $type)
	{
		if (empty(env('MUSIXMATCH_TOKEN')))
			return response()->json(['message' => 'Musixmatch token was not found'], 500);
		else if (!in_array($type, ['subtitle', 'richsync', 'lyrics']))
			return response()->json(['message' => 'Invalid lyric type request'], 400);
		Sleep::for(5)->seconds();
		try {
			$response = Http::withHeaders([
				"cookie" => "AWSELBCORS=0; AWSELB=0"
			])->get('https://apic-desktop.musixmatch.com/ws/1.1/track.' . $type . '.get', [
				'user_language' => 'en',
				'app_id' => 'web-desktop-app-v1.0',
				'commontrack_id' => $id,
				'usertoken' => env('MUSIXMATCH_TOKEN')
			]);
			$r = json_decode($response->body(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
				return response()->json([
					'message' => 'Error parsing JSON response: ' . json_last_error_msg()
				], 500);
			}
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return response()->json([
					'message' => $this->getMXerror($header)
				], $header['status_code']);
			}
			if ($type === 'subtitle') {
				$data = $r['message']['body']['subtitle'];
				if ($data['restricted'] === true)
					return response()->json(['message' => 'This lyric is restricted'], 403);
				$lyrics = [
					'content' => $data['subtitle_body'],
					'copyright' => $data['lyrics_copyright'],
					'duration' => gmdate('i:s', $data['subtitle_length'])
				];
			} else if ($type === 'richsync') {
				$data = $r['message']['body']['richsync'];
				if ($data['restricted'] === true)
					return response()->json(['message' => 'This lyric is restricted'], 403);
				$lyrics = [
					'content' => $this->richsync(json_decode($data['richsync_body'], true)),
					'copyright' => $data['lyrics_copyright'],
					'duration' => gmdate('i:s', $data['richsync_length'])
				];
			} else { //plain
				$data = $r['message']['body']['lyrics'];
				if ($data['restricted'] === true)
					return response()->json(['message' => 'This lyric is restricted'], 403);
				$lyrics = [
					'content' => $data['lyrics_body'],
					'copyright' => $data['lyrics_copyright']
				];
			}
			return response()->json($lyrics);
		} catch (ConnectionException $th) {
			Log::error($th);
			return response()->json([
				'message' => 'Musixmatch connection failed: ' . $th->getMessage()
			], 500);
		}
	}
	private function richsync($lrc)
	{
		$richsync = '';
		// $lines = count($lrc);
		$linenum = 0;
		foreach ($lrc as $line) {
			$linenum++;
			$words = count($line['l']);
			$wordnum = 0;
			if ($linenum === 1) {
				if ($line['ts'] > 5)
					$richsync .= "[" . $this->formatTime($line['ts'] - 5) . ']';
				else $richsync .= "[00:00.00]";
			} else $richsync .= "[" . $this->formatTime($line['ts']) . ']';
			foreach ($line['l'] as $word) {
				$wordnum++;
				$richsync .= '<' . $this->formatTime($line['ts'] + $word['o']) . '>' . $word['c'];
				if ($wordnum === $words)
					$richsync .= '<' . $this->formatTime($line['te']) . "> \n";
			}
			// if ($linenum === $lines)
			// 	$richsync .= "[" . $this->formatTime($line['te'] + 5) . "]\n";
		}
		return $richsync;
	}
}
