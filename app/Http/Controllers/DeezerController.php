<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use JsonException;

class DeezerController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate([
				'query' => 'required',
				'offset' => 'nullable|integer|min:0|multiple_of:20'
			]);
			$r = Http::retry(3, 100)->timeout(25000)->get(
				'https://api.deezer.com/search/track',
				['limit' => 20, 'q' => $req['query'], 'index' => $req['offset'] ?? 0]
			)->json(null, null, JSON_THROW_ON_ERROR);
			return response()->json(['html' => view('deezer.list', $r)->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			Log::error($th);
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				match (get_class($th)) {
					JsonException::class => "Error parsing response: {$th->getMessage()}",
					ConnectionException::class => "Deezer API connection error {$th->getCode()}: {$th->getMessage()}",
					RequestException::class => "Deezer API error {$th->response->status()}",
					default => "Deezer API unexpected error: {$th->getMessage()}"
				}
			);
		}
	}
	public function get(int $id)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)->withHeaders([
				'Authorization' => 'Bearer '.env('PAXSENIX_TOKEN','sk-paxsenix-ABC123')
			])->get(parent::$paxsenix_url . 'lyrics/deezer', ['id' => $id])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Deezer API error: ', $r);
				abort(500, 'Oops, something went wrong with Deezer API. Please try again later.');
			}
			if (empty($r['synchronizedLines'])) $synced = null;
			else {
				$prevtime = 0;
				$synced = $lastTime = '';
				foreach ($r['synchronizedLines'] as $idx => $line) {
					if (($line['milliseconds'] - $prevtime) > 5000 && $idx !== 0)
						$synced .= sprintf("[%s]\n", parent::formatTime($prevtime, true));
					$synced .= $line['lrcTimestamp'] .= $line['line'] . "\n";
					if ($idx === count($r['synchronizedLines']) - 1) {
						$lastTime = '[' . parent::formatTime($line['milliseconds'] + $line['duration'], true) . ']';
						$synced .= $lastTime;
					}
					$prevtime = $line['milliseconds'] + $line['duration'];
				}
			}
			if (empty($r['synchronizedWordByWordLines'])) $wbw = null;
			else {
				$prevtime = 0;
				$wbw = '';
				foreach ($r['synchronizedWordByWordLines'] as $idx => $line) {
					if ($idx === 0) {
						$wbw .= ($line['start'] <= 3000)
							? '[00:00.00]'
							: "[" . parent::formatTime($line['start'] - mt_rand(2500, 3000), true) . "]";
					} elseif (($line['start'] - $prevtime) > 9000) {
						$wbw .= sprintf(
							"[%s]\n[%s]",
							parent::formatTime($prevtime + mt_rand(2500, 3500), true),
							parent::formatTime($line['start'] - mt_rand(2500, 3500), true)
						);
					} else $wbw .= sprintf("[%s]", parent::formatTime($line['start'], true));
					foreach ($line['words'] as $syl) $wbw .= self::mergeSyl($syl);
					$wbw .= "\n";
					if ($idx === count($r['synchronizedWordByWordLines']) - 1)
						$wbw .= $lastTime ?? '[' . parent::formatTime($line['end'], true) . ']';
					$prevtime = $line['end'];
				}
			}
			return response()->json([
				'plain' => $r['text'],
				'synced' => $synced,
				'wbw' => $wbw,
				'id' => $r['id'],
				'writer' => $r['writers'],
				'copyright' => $r['copyright'],
				'license' => $r['licence']
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	private static function mergeSyl(array $syl): string
	{
		$part = sprintf(
			"<%s>%s<%s> ",
			parent::formatTime($syl['start'], true),
			$syl['word'],
			parent::formatTime($syl['end'], true)
		);
		return $part;
	}
}
