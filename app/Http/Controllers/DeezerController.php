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
			if ($r['isError']) {
				Log::error($r);
				abort(404, $r['error']);
			} else if (empty($r['lyrics'])) $synced = null;
			else {
				$synced = '';
				$linenum = 0;
				foreach ($r['lyrics'] as $line) {
					$linenum++;
					if ($linenum === 1 && count($line['text']) > 1) {
						if ($line['timestamp'] <= 5000)
							$synced .= '[00:00.00]';
						else
							$synced .= '[' . $this->formatTime($line['timestamp'] / 1000 - 5) . ']';
					} else
						$synced .= '[' . $this->formatTime($line['timestamp'] / 1000) . ']';
					if (count($line['text']) > 1) {
						$sylnum = 0;
						foreach ($line['text'] as $syl) {
							$sylnum++;
							// if($line['timestamp']===$syl['timestamp'])
							$synced .=
								'<' . $this->formatTime($syl['timestamp'] / 1000) . '>' .
								$syl['text'] .
								'<' . $this->formatTime($syl['endtime'] / 1000) .
								($syl['part'] === false ? '> ' : '>');
						}
						$synced .= "\n";
					} else $synced .= $line['text'][0]['text'] . "\n";
					if ($linenum === count($r['lyrics']))
						$synced .= '[' . $this->formatTime($line['endtime'] / 1000) . "]\n";
				}
			}
			$data = [
				'plain' => $r['plain_lyrics'],
				'synced' => $synced,
				'id' => $r['id'],
				'writer' => $r['writers'],
			];
			return response()->json($data);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Deezer connection failed: ' . $th->getMessage());
		}
	}
}
