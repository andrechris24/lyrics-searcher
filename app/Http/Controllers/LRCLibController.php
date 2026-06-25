<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Validation\ValidationException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class LRCLibController extends Controller
{
	public static string $url = 'https://lrclib.net/api/search';
	public function standard(Request $request)
	{
		try {
			$request->validate(['query' => 'required']);
			$response = Http::retry(3, 100)->get(self::$url, ['q' => $request['query']]);
			$data = self::decodeJson($response->body());
			if ($data === false) {
				return to_route('lrclib.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('lrclib.result', compact('data'));
		} catch (ConnectionException $e) {
			Log::error($e);
			return to_route('lrclib.index')->withInput()
				->withError('LRCLib connection failed: ' . $e->getMessage());
		} catch (ValidationException $e) {
			return to_route('lrclib.index')->withInput()->withErrors($e->errors());
		}
	}
	public function advanced(Request $request)
	{
		try {
			$request->validate(['title' => 'required']);
			$response = Http::retry(3, 100)->get(self::$url, [
				'track_name' => $request['title'],
				'artist_name' => $request['artist'],
				'album_name' => $request['album']
			]);
			$data = self::decodeJson($response->body());
			if ($data === false) {
				return to_route('lrclib.advanced')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('lrclib.advanced.result', compact('data'));
		} catch (ConnectionException $e) {
			Log::error($e);
			return to_route('lrclib.advanced')->withInput()
				->withError('LRCLib connection failed: ' . $e->getMessage());
		} catch (ValidationException $e) {
			return to_route('lrclib.advanced')->withInput()->withErrors($e->errors());
		}
	}
	public function convert(Request $req)
	{
		$req->validate(['content' => 'required']);
		$prevtime = 0;
		try {
			$yaml = Yaml::parse($req['content']);
			$lyricsfile = '[ti: ' . $yaml['metadata']['title'] .
				"]\n[ar: " . $yaml['metadata']['artist'] . "]\n";
			if (array_key_exists('album', $yaml['metadata']))
				$lyricsfile .= "[al: " . $yaml['metadata']['album'] . "]\n";
			if (array_key_exists('duration_ms', $yaml['metadata']))
				$lyricsfile .= "[length: " . gmdate('i:s', floor($yaml['metadata']['duration_ms'] / 1000)) . "]\n";
			if (array_key_exists('offset_ms', $yaml['metadata']))
				$lyricsfile .= "[offset: " . $yaml['metadata']['duration_ms'] . "]\n";
			if (!array_key_exists('instrumental', $yaml['metadata']) || $yaml['metadata']['instrumental'] === false) {
				$lyricsfile .= "[by: LRCLib]\n[ve: " . $yaml['version'] . "]\n";
				foreach ($yaml['lines'] as $idx => $line) {
					if ($idx === 0) {
						if ($line['start_ms'] > 3000)
							$lyricsfile .= "[" . $this->formatTime(($line['start_ms'] - rand(2500, 3000)) / 1000) . ']';
						else $lyricsfile .= "[00:00.00]";
					} else if (($line['start_ms'] - $prevtime) > 9000) {
						$lyricsfile .= "[" . $this->formatTime(($prevtime + rand(2500, 3500)) / 1000) . "]\n";
						$lyricsfile .= "[" . $this->formatTime(($line['start_ms'] - rand(2500, 3500)) / 1000) . ']';
					} else
						$lyricsfile .= "[" . $this->formatTime($line['start_ms'] / 1000) . ']';
					foreach ($line['words'] as $word) {
						$lyricsfile .= '<' . $this->formatTime($word['start_ms'] / 1000) . '>' . $word['text'];
					}
					if (array_key_exists('end_ms', $line)) {
						$prevtime = $line['end_ms'];
						$lyricsfile .= '<' . $this->formatTime(floor($line['end_ms'] / 1000)) . "> \n";
					} else $lyricsfile .= "\n";
				}
			} else return response()->json(['instrumental' => true, 'contents' => $yaml]);
			return response()->json(['instrumental' => false, 'lrc' => $lyricsfile]);
		} catch (ParseException $e) {
			Log::error($e);
			abort(500, $e->getMessage());
		}
	}
}
