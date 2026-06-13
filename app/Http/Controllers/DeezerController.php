<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\ValidationException;

class DeezerController extends Controller
{
	public function search(Request $req)
	{
		try {
			$req->validate([
				'query' => 'required',
				'offset' => 'nullable|integer|min:0|multiple_of:20'
			]);
			$response = Http::connectTimeout(30)->get(
				'https://api.deezer.com/search/track',
				['limit' => 20, 'q' => $req['query'], 'index' => $req['offset'] ?? 0]
			);
			$r = self::decodeJson($response->body());
			if ($r === false) {
				return to_route('deezer.index')->withInput()
					->withError('Error parsing response: ' . json_last_error_msg());
			}
			return view('deezer.result', $r);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('deezer.index')->withInput()
				->withError('Deezer connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('deezer.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::connectTimeout(30)
				->get('https://lyrics.paxsenix.org/deezer/lyrics', ['id' => $id]);
			$r = self::decodeJson($response->body());
			abort_if($r === false, 500, 'Error parsing response: ' . json_last_error_msg());
			$prevtime = 0;
			if ($r['isError']) {
				Log::error($r);
				abort(404, $r['error']);
			} else if (empty($r['lyrics'])) $synced = null;
			else {
				$synced = '';
				foreach ($r['lyrics'] as $idx => $line) {
					if (count($line['text']) > 1) {
						if ($idx === 0) {
							if ($line['timestamp'] <= 3000) $synced .= '[00:00.00]';
							else
								$synced .= '[' . $this->formatTime($line['timestamp'] / 1000 - 3) . ']';
						} elseif (($line['timestamp'] - $prevtime) > 9000) {
							$synced .= "[" . $this->formatTime($prevtime / 1000 + 3) . "]\n";
							$synced .= "[" . $this->formatTime($line['timestamp'] / 1000 - 3) . ']';
						} else
							$synced .= "[" . $this->formatTime($line['timestamp'] / 1000) . ']';
						foreach ($line['text'] as $syl) {
							$synced .=
								'<' . $this->formatTime($syl['timestamp'] / 1000) . '>' .
								$syl['text'] .
								'<' . $this->formatTime($syl['endtime'] / 1000) .
								($syl['part'] === false ? '> ' : '>');
						}
						$synced .= "\n";
					} else {
						if (($line['timestamp'] - $prevtime) > 5000 && $idx !== 0)
							$synced .= '[' . $this->formatTime($line['endtime'] / 1000) . "]\n";
						$synced .= '[' . $this->formatTime($line['timestamp'] / 1000) . ']';
						$synced .= $line['text'][0]['text'] . "\n";
					}
					if ($idx === count($r['lyrics']) - 1)
						$synced .= '[' . $this->formatTime($line['endtime'] / 1000) . "]\n";
					$prevtime = $line['endtime'];
				}
			}
			$data = [
				'plain' => $r['plain_lyrics'],
				'synced' => $synced,
				'id' => $r['id'],
				'writer' => $r['writers']
			];
			return response()->json($data);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Deezer connection failed: ' . $th->getMessage());
		}
	}
}
