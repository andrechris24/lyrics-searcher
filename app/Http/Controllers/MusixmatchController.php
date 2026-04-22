<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Sleep; //Required as Musixmatch has strict rate limit
use Illuminate\Validation\ValidationException;

class MusixmatchController extends Controller
{
	public const array MX_HEADER = ["cookie" => "AWSELBCORS=0; AWSELB=0"];
	public static string $url = 'https://apic-desktop.musixmatch.com/ws/1.1/';
	public function standard(Request $req)
	{
		if (empty(env('MUSIXMATCH_TOKEN')))
			return to_route('index')->withError('Musixmatch token was not found');
		try {
			$req->validate(['query' => 'required', 'page' => 'nullable|integer|min:1']);
			Sleep::for(5)->seconds();
			$response = Http::withHeaders(self::MX_HEADER)->get(self::$url . 'track.search', [
				'user_language' => 'en',
				'app_id' => 'web-desktop-app-v1.0',
				'q' => $req['query'],
				'usertoken' => env('MUSIXMATCH_TOKEN'),
				'page' => $req['page'] ?? 1,
				'page_size' => 20, //Match LRCLib result count
				// 's_artist_rating' => 'desc',
				'f_has_lyrics' => 1 //Search tracks with lyrics only
			]);
			$r = self::decodeJson($response->body());
			if ($r === false) {
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
			]);
			Sleep::for(5)->seconds();
			$response = Http::withHeaders(self::MX_HEADER)->get(self::$url . 'track.search', [
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
			$r = self::decodeJson($response->body());
			if ($r === false) {
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
			$response = Http::withHeaders(self::MX_HEADER)
				->get(self::$url . 'chart.tracks.get', [
					'user_language' => 'en',
					'app_id' => 'web-desktop-app-v1.0',
					'country' => 'id',
					'chart_name' => $type,
					'usertoken' => env('MUSIXMATCH_TOKEN'),
					'f_has_lyrics' => 1
				]);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('musixmatch.index')->withInput()
					->withError('Error parsing JSON response: ' . json_last_error_msg());
			}
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200)
				return to_route('musixmatch.index')->withError($this->getMXerror($header));
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
		abort_if(empty(env('MUSIXMATCH_TOKEN')), 500, 'Musixmatch token was not found');
		abort_if(
			!in_array($type, ['subtitle', 'richsync', 'lyrics']),
			400,
			'Invalid lyric type request'
		);
		Sleep::for(5)->seconds();
		try {
			$response = Http::withHeaders(self::MX_HEADER)
				->get(self::$url . 'track.' . $type . '.get', [
					'user_language' => 'en',
					'app_id' => 'web-desktop-app-v1.0',
					'commontrack_id' => $id,
					'usertoken' => env('MUSIXMATCH_TOKEN')
				]);
			$r = self::decodeJson($response->body());
			abort_if(
				$r === false,
				500,
				'Error parsing JSON response: ' . json_last_error_msg()
			);
			$header = $r['message']['header'];
			abort_if(
				$header['status_code'] !== 200,
				$header['status_code'],
				$this->getMXerror($header)
			);
			$data = $r['message']['body'][$type];
			abort_if($data['restricted'] === true, 403, 'This lyric is restricted');
			$lyrics = match ($type) {
				'subtitle' => [
					'content' => $data['subtitle_body'],
					'id' => $data['subtitle_id'],
					'duration' => gmdate('i:s', $data['subtitle_length'])
				],
				'richsync' => [
					'content' => $this->richsync(json_decode($data['richsync_body'], true)),
					'id' => $data['richsync_id'],
					'duration' => gmdate('i:s', $data['richsync_length'])
				],
				default => ['content' => $data['lyrics_body']]
			};
			return response()->json($lyrics);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Musixmatch connection failed: ' . $th->getMessage());
		}
	}
	private function richsync($lrc)
	{
		$richsync = '';
		$linenum = 0;
		foreach ($lrc as $line) {
			$linenum++;
			if ($linenum === 1) {
				if ($line['ts'] > 5)
					$richsync .= "[" . $this->formatTime($line['ts'] - 5) . ']';
				else $richsync .= "[00:00.00]";
			} else $richsync .= "[" . $this->formatTime($line['ts']) . ']';
			foreach ($line['l'] as $word) {
				$richsync .= '<' . $this->formatTime($line['ts'] + $word['o']) . '>' . $word['c'];
			}
			$richsync .= '<' . $this->formatTime($line['te']) . "> \r\n";
		}
		return $richsync;
	}
}
