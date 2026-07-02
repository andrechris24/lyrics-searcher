<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Validation\ValidationException;
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
			$response = Http::retry(3, 100)->timeout(25000)->get(
				'https://api.deezer.com/search/track',
				['limit' => 20, 'q' => $req['query'], 'index' => $req['offset'] ?? 0]
			);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			return view('deezer.result', $r);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('deezer.index')->withInput()
				->withError('Deezer connection error ' . $th->getCode() . ': ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('deezer.index')->withInput()->withErrors($e->errors());
		} catch (RequestException $e) {
			Log::error($e);
			return to_route('deezer.index')->withInput()
				->withError('Deezer HTTP Error ' . $e->response->status());
		}catch(JsonException $e){
			Log::error($e);
			return to_route('deezer.index')->withInput()
					->withError('Error parsing response: ' . $e->getMessage());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)
				->get('https://lyrics.paxsenix.org/deezer/lyrics', ['id' => $id]);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			$prevtime = 0;
			if ($r['isError']) {
				abort_if($r['error'] === 'No lyrics found', 404, 'No lyric available for this song');
				Log::error('Deezer API error: ', $r);
				abort(500, $r['error']);
			} else if (empty($r['lyrics'])) $synced = null;
			else {
				$synced = '';
				foreach ($r['lyrics'] as $idx => $line) {
					if (count($line['text']) > 1) {
						if ($idx === 0) {
							if ($line['timestamp'] <= 3000) $synced .= '[00:00.00]';
							else
								$synced .= '[' . $this->formatTime(($line['timestamp'] - mt_rand(2500, 3000)) / 1000) . ']';
						} elseif (($line['timestamp'] - $prevtime) > 9000) {
							$synced .= "[" . $this->formatTime(($prevtime + mt_rand(2500, 3500)) / 1000) . "]\n";
							$synced .= "[" . $this->formatTime(($line['timestamp'] - mt_rand(2500, 3500)) / 1000) . ']';
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
							$synced .= '[' . $this->formatTime($prevtime / 1000) . "]\n";
						$synced .= '[' . $this->formatTime($line['timestamp'] / 1000) . ']';
						$synced .= $line['text'][0]['text'] . "\n";
					}
					if ($idx === count($r['lyrics']) - 1)
						$synced .= '[' . $this->formatTime($line['endtime'] / 1000) . "]\n";
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
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'Deezer connection error ' . $th->getCode() . ': ' . $th->getMessage());
		} catch (RequestException $e) {
			Log::error($e);
			abort(
				$e->response->status(),
				$e->response->status() === 404 ? 'No lyric available for this song' : 'Deezer API error ' . $e->response->status()
			);
		}catch(JsonException $e){
			Log::error($e);
			abort(500, 'Error parsing response: ' . $e->getMessage());
		}
	}
}
