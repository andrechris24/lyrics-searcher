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
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function get(int $id)
	{
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . 'deezer/lyrics', ['id' => $id])->json(null, null, JSON_THROW_ON_ERROR);
			$prevtime = 0;
			if ($r['isError']) {
				abort_if($r['error'] === 'No lyrics found', 404, 'No lyric available for this song');
				Log::error('Deezer API error: ', $r);
				abort(500, 'Oops, something went wrong with Deezer API. Please try again later.');
			} else if (empty($r['lyrics'])) $synced = null;
			else {
				$synced = '';
				foreach ($r['lyrics'] as $idx => $line) {
					if (count($line['text']) > 1) {
						if ($idx === 0) {
							$synced .= ($line['timestamp'] <= 3000)
								? '[00:00.00]'
								: "[" . parent::formatTime(($line['timestamp'] - mt_rand(2500, 3000)) / 1000) . "]";
						} elseif (($line['timestamp'] - $prevtime) > 9000) {
							$synced .= sprintf(
								"[%s]\n[%s]",
								parent::formatTime(($prevtime + mt_rand(2500, 3500)) / 1000),
								parent::formatTime(($line['timestamp'] - mt_rand(2500, 3500)) / 1000)
							);
						} else $synced .= sprintf("[%s]", parent::formatTime($line['timestamp'] / 1000));
						foreach ($line['text'] as $syl) $synced .= self::mergeSyl($syl);
						$synced .= "\n";
						if ($line['background'] === true) {
							$synced .= '[bg: ';
							foreach ($line['backgroundText'] as $bg)
								$synced .= self::mergeSyl($bg);
							$synced .= "]\n";
						}
					} else {
						if (($line['timestamp'] - $prevtime) > 5000 && $idx !== 0)
							$synced .= sprintf("[%s]\n", parent::formatTime($prevtime / 1000));
						$synced .= sprintf(
							"[%s]%s\n",
							parent::formatTime($line['timestamp'] / 1000),
							$line['text'][0]['text']
						);
					}
					if ($idx === count($r['lyrics']) - 1)
						$synced .= '[' . parent::formatTime($line['endtime'] / 1000) . ']';
					$prevtime = $line['endtime'];
				}
			}
			return response()->json([
				'plain' => $r['plain_lyrics'],
				'synced' => $synced,
				'id' => $r['id'],
				'writer' => $r['writers'],
				'copyright' => $r['copyright'],
				'license' => $r['licence']
			]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		if (get_class($ex) === RequestException::class) {
			Log::warning($ex->response->effectiveUri());
			if ($ex->response->status() !== 404) Log::error($ex);
		}
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Deezer API connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => $ex->response->status() === 404 ? 'No result' : "Deezer API error {$ex->response->status()}",
			default => "Deezer API unexpected error: {$ex->getMessage()}"
		};
	}
	private static function mergeSyl(array $syl): string
	{
		$part = sprintf(
			"<%s>%s",
			parent::formatTime($syl['timestamp'] / 1000),
			$syl['text']
		);
		if ($syl['part'] === false)
			$part .= sprintf("<%s> ", parent::formatTime($syl['endtime'] / 1000));
		return $part;
	}
}
