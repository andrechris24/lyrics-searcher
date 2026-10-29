<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpleDownloadRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Http};
use JsonException;

class AppleController extends Controller
{
	public function search(Request $req)
	{
		$req->validate(['query' => 'required', 'api' => 'required|in:itunes,paxsenix']);
		try {
			if ($req['api'] === 'paxsenix') {
				abort_if(
					empty(env('PAXSENIX_TOKEN')),
					401,
					'API Token is required for Paxsenix request and song downloads'
				);
				$r = Http::retry(2, 100)->timeout(25000)
					->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
					->get(parent::$paxsenix_url . "apple-music/search", ['q' => $req['query']])
					->json(null, null, JSON_THROW_ON_ERROR);
			} else {
				$r = Http::retry(3, 100)->timeout(25000)->get(
					"https://itunes.apple.com/search",
					['term' => $req['query'], 'entity' => 'song']
				)->json(null, null, JSON_THROW_ON_ERROR);
			}
			return response()->json(['html' => view('apple.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort_if(
				$req['api'] === 'paxsenix',
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				'Error loading results: ' . parent::lyricallyError($th, 'paxsenix')
			);
			Log::error($th);
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				'Error loading results: ' . match (get_class($th)) {
					JsonException::class => "Malformed response ({$th->getMessage()})",
					ConnectionException::class => "Apple Music connection error, {$th->getMessage()}",
					RequestException::class => "Apple Music HTTP Error {$th->response->status()}",
					default => "Apple Music unexpected error"
				}
			);
		}
	}
	public function get(Request $req)
	{
		$req->validate(['id' => 'required|integer', 'source' => 'required|in:lyrically,paxsenix,zarz']);
		try {
			$http = Http::retry(2, 100)->timeout(25000);
			switch ($req['source']) {
				case 'lyrically':
					$r = $http->withHeaders(parent::APP_HEADER)
						->get(parent::$lyrically_url . "apple-music/lyrics", ['id' => $req['id']])
						->json(null, null, JSON_THROW_ON_ERROR);
					break;
				case 'paxsenix':
					abort_if(
						empty(env('PAXSENIX_TOKEN')),
						401,
						'API Token is required for Paxsenix request and song downloads'
					);
					$r = $http->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
						->get(parent::$paxsenix_url . "lyrics/applemusic", ['id' => $req['id']])
						->json(null, null, JSON_THROW_ON_ERROR);
					break;
				case 'zarz':
					$r = $http->get("https://api.zarz.moe/apple-music/lyrics", ['id' => $req['id']])
						->json(null, null, JSON_THROW_ON_ERROR);
					break;
				default:
					abort(400, 'Invalid lyric source provided');
					break;
			}
			if (array_key_exists('error', $r)) {
				Log::error("Apple Music API error: {$r['message']}", $r);
				abort(500, 'Oops, an error occurred with ' . ucfirst($req['source']) . ' API.');
			}
			return response()->json([
				'id' => $req['id'],
				'plain' => $r['plain'],
				'synced' => $r['lrc'],
				'syllable' => $r['type'] === 'Syllable' ? self::multiSyl($r['content']) : null,
				'multisyl' => $r['type'] === 'Syllable' ? self::multiSyl($r['content'], true) : null,
				'ttml' => $r['ttmlContent'] ?? $r['ttml_content'],
				'type' => $r['type'],
				'writers' => implode(', ', $r['metadata']['songwriters']),
				'length' => gmdate('i:s', round($r['metadata']['duration'] / 1000, 0, PHP_ROUND_HALF_UP))
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				'Error retrieving lyric: ' . parent::lyricallyError($th, $req['source'])
			);
		}
	}
	public function download(SimpleDownloadRequest $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API Token is required for Paxsenix request and song downloads'
		);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get(parent::$paxsenix_url . 'dl/applemusic', ['url' => $req['url']])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Apple Music API error: ', $r);
				abort(
					500,
					'Oops, something went wrong while downloading song. Please try again later.'
				);
			}
			// Log::debug($r);
			return response()->json($r);
		} catch (ConnectionException | RequestException | JsonException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Download failed: ' . parent::lyricallyError($e, 'paxsenix')
			);
		}
	}
	private static function multiSyl(array $content, bool $multi = false): string|null
	{
		$body = '';
		$prevtime = 0;
		foreach ($content as $idx => $line) {
			if (($line['timestamp'] - $prevtime) > 5000 && $idx !== 0) {
				if ($multi) {
					$body .= sprintf(
						"[%s]\n[%s]%s: ",
						parent::formatTime($prevtime + 1, true),
						parent::formatTime($line['timestamp'], true),
						$line['agent']
					);
				} else {
					$body .= sprintf(
						"[%s]\n[%s]",
						parent::formatTime($prevtime, true),
						parent::formatTime($line['timestamp'], true),
					);
				}
			} else {
				if ($multi)
					$body .= '[' . parent::formatTime($line['timestamp'], true) . "]{$line['agent']}: ";
				else $body .= '[' . parent::formatTime($line['timestamp'], true) . ']';
			}
			foreach ($line['text'] as $syl) {
				$part = sprintf(
					"<%s>%s",
					parent::formatTime($syl['timestamp'], true),
					$syl['text']
				);
				if ($syl['part'] === false)
					$part .= sprintf("<%s> ", parent::formatTime($syl['endtime'], true));
				$body .= $part;
			}
			if (env('MINILYRICS_COMPATIBLE', false))
				$body .= "<" . parent::formatTime($line['endtime'], true) . ">" . PHP_EOL;
			if ($line['background'] === true) {
				$body .= '[bg: ';
				foreach ($line['backgroundText'] as $syl) {
					$part = sprintf(
						"<%s>%s",
						parent::formatTime($syl['timestamp'], true),
						$syl['text']
					);
					if ($syl['part'] === false)
						$part .= sprintf("<%s> ", parent::formatTime($syl['endtime'], true));
					$body .= $part;
				}
				$body .= ']' . PHP_EOL;
			}
			$prevtime = $line['endtime'];
		}
		return $body . '[' . parent::formatTime($content[count($content) - 1]['sectionEnd'] + 1, true) . ']' . PHP_EOL;
	}
}
