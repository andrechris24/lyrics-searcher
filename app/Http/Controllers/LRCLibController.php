<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Http, Log};
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use JsonException;

class LRCLibController extends Controller
{
	public static string $url = 'https://lrclib.net/api/search';
	public function standard(Request $request)
	{
		try {
			$request->validate(['query' => 'required']);
			$data = Http::retry(3, 100)->timeout(25000)
				->get(self::$url, ['q' => $request['query']])
				->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('lrclib.list', compact('data'))->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function advanced(Request $request)
	{
		try {
			$request->validate(['title' => 'required']);
			$data = Http::retry(3, 100)->timeout(25000)->get(self::$url, [
				'track_name' => $request['title'],
				'artist_name' => $request['artist'],
				'album_name' => $request['album']
			])->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('lrclib.list', compact('data'))->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function convert(Request $req)
	{
		$req->validate(['content' => 'required']);
		$prevtime = 0;
		try {
			$yaml = Yaml::parse($req['content']);
			$meta = [
				'ti' => $yaml['metadata']['title'],
				'ar' => $yaml['metadata']['artist'],
				'al' => array_key_exists('album', $yaml['metadata']) ? $yaml['metadata']['album'] : '',
				'length' => array_key_exists('duration_ms', $yaml['metadata']) ? gmdate('i:s', round($yaml['metadata']['duration_ms'] / 1000, 0, PHP_ROUND_HALF_UP)) : '00:00',
				'offset' => array_key_exists('offset_ms', $yaml['metadata']) ? $yaml['metadata']['offset_ms'] : 0,
				'by' => 'LRCLib',
				've' => $yaml['version']
			];
			$lyricsfile = '';
			foreach ($meta as $key => $value) {
				$lyricsfile .= sprintf("[%s: %s]\n", $key, $value);
			}
			foreach ($yaml['lines'] as $idx => $line) {
				if ($idx === 0) {
					if ($line['start_ms'] > 3000)
						$lyricsfile .= "[" . parent::formatTime(($line['start_ms'] - mt_rand(2500, 3000)) / 1000) . "]";
					else $lyricsfile .= "[00:00.00]";
				} else if (($line['start_ms'] - $prevtime) > 9000) {
					$lyricsfile .= sprintf(
						"[%s]\n[%s]",
						parent::formatTime(($prevtime + mt_rand(2500, 3500)) / 1000),
						parent::formatTime(($line['start_ms'] - mt_rand(2500, 3500)) / 1000)
					);
				} else $lyricsfile .= "[" . parent::formatTime($line['start_ms'] / 1000) . "]";
				foreach ($line['words'] as $word) {
					$lyricsfile .= sprintf(
						"<%s>%s",
						parent::formatTime($word['start_ms'] / 1000),
						$word['text']
					);
				}
				if (array_key_exists('end_ms', $line)) {
					$prevtime = $line['end_ms'];
					$lyricsfile .= sprintf(
						env("MINILYRICS_COMPATIBLE") ? "<%s> \n" : "<%s>\n",
						parent::formatTime($line['end_ms'] / 1000)
					);
				} else $lyricsfile .= "\n";
			}
			return response()->json([
				'instrumental' => array_key_exists('instrumental', $yaml['metadata']) && $yaml['metadata']['instrumental'] == true,
				'lrc' => $lyricsfile
			]);
		} catch (ParseException $e) {
			Log::error($e);
			abort(500, 'Oops, there was an error in word-by-word lyric content');
		}
	}
	private static function matchError(mixed $ex): string
	{
		if (get_class($ex) === RequestException::class && $ex->response->status() !== 404)
			Log::error($ex);
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "LRCLib connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "LRCLib HTTP Error {$ex->response->status()}",
			default => "LRCLib unexpected error: {$ex->getMessage()}"
		};
	}
}
