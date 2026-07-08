<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
// use Illuminate\Support\Sleep; //Required as Musixmatch has strict rate limit
use Illuminate\Validation\ValidationException;
use JsonException;

class MusixmatchController extends Controller
{
	public const array MX_HEADER = ["cookie" => "AWSELBCORS=0; AWSELB=0"];
	public static string $url = 'https://apic-desktop.musixmatch.com/ws/1.1/';
	public static array $query = [
		'user_language' => 'en',
		'app_id' => 'web-desktop-app-v1.0',
		'page_size' => 20,
		'f_has_lyrics' => 1
	];
	public function standard(Request $req)
	{
		try {
			$req->validate([
				'query' => 'required',
				'type' => 'required|in:all,track,artist,lyrics,track_artist,writer',
				'page' => 'nullable|integer|min:1'
			]);
			// Sleep::for(5)->seconds();
			$query = self::$query;
			$query['usertoken'] = self::validateToken();
			$query['page'] = $req['page'] ?? 1;
			switch ($req['type']) {
				case 'track':
					$query['q_track'] = $req['query'];
					break;
				case 'artist':
					$query['q_artist'] = $req['query'];
					break;
				case 'lyrics':
					$query['q_lyrics'] = $req['query'];
					break;
				case 'track_artist':
					$query['q_track_artist'] = $req['query'];
					break;
				case 'writer':
					$query['q_writer'] = $req['query'];
					break;
				default:
					$query['q'] = $req['query'];
					break;
			}
			$response = Http::retry(3, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)->get(self::$url . 'track.search', $query);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.index')->withInput()
					->withError($this->getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.result', compact('data', 'header'));
		} catch (ValidationException $e) {
			return to_route('musixmatch.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('musixmatch.index')->withInput()->withErrors($message);
		}
	}
	public function advanced(Request $req)
	{
		try {
			$req->validate([
				'title' => 'nullable|required_without_all:artist,lyrics|string',
				'artist' => 'nullable|required_without_all:title,lyrics|string',
				'lyrics' => 'nullable|required_without_all:title,artist|string',
				'page' => 'nullable|integer|min:1'
			]);
			// Sleep::for(5)->seconds();
			$query = self::$query;
			$query['usertoken'] = self::validateToken();
			$query['q_track'] = $req['title'];
			$query['q_artist'] = $req['artist'];
			$query['q_lyrics'] = $req['lyrics'];
			$query['page'] = $req['page'] ?? 1;
			$response = Http::retry(3, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)->get(self::$url . 'track.search', $query);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.advanced')->withInput()
					->withError($this->getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.advanced.result', compact('data', 'header'));
		} catch (ValidationException $e) {
			return to_route('musixmatch.advanced')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('musixmatch.advanced')->withInput()->withError($message);
		}
	}
	public function charts(string $type)
	{
		if (!in_array($type, ['top', 'hot']))
			return to_route('musixmatch.index')->withError('Unrecognized parameter for Musixmatch Chart');
		// Sleep::for(5)->seconds();
		$query = self::$query;
		$query['usertoken'] = self::validateToken();
		$query['chart_name'] = $type;
		$query['country'] = 'id';
		try {
			$response = Http::retry(3, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)->get(self::$url . 'chart.tracks.get', $query);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200)
				return to_route('musixmatch.index')->withError($this->getMXerror($header));
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.chart', compact('data', 'header'));
		} catch (ValidationException $e) {
			return to_route('musixmatch.index')->withErrors($e->errors());
		} catch (ConnectionException | JsonException | \Exception $th) {
			Log::error($th);
			$message = self::matchError($th);
			return to_route('musixmatch.index')->withError($message);
		}
	}
	public function get(int $id, string $type)
	{
		abort_if(
			!in_array($type, ['subtitle', 'richsync', 'lyrics']),
			400,
			'Invalid lyric type request'
		);
		// Sleep::for(5)->seconds();
		$query = self::$query;
		$query['usertoken'] = self::validateToken();
		$query['commontrack_id'] = $id;
		unset($query['f_has_lyrics'], $query['page_size']);
		try {
			$response = Http::retry(3, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)
				->get(self::$url . 'track.' . $type . '.get', $query);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
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
					'content' => $this->richsync(parent::decodeJson($data['richsync_body'])),
					'id' => $data['richsync_id'],
					'duration' => gmdate('i:s', $data['richsync_length'])
				],
				default => ['content' => $data['lyrics_body']]
			};
			return response()->json($lyrics);
		} catch (ConnectionException | JsonException $th) {
			Log::error($th);
			$message = self::matchError($th);
			abort(500, $message);
		}
	}
	private function richsync(array $lrc): ?string
	{
		if (empty($lrc) || $lrc === false) return null;
		$richsync = '';
		$prevtime = 0;
		foreach ($lrc as $idx => $line) {
			if ($idx === 0) {
				$richsync .= ($line['ts'] > 3) ?
					"[{$this->formatTime($line['ts'] - 3)}]" :
					"[00:00.00]";
			} else {
				$richsync .= (($line['ts'] - $prevtime) > 9)
					? sprintf(
						"[%s]\n[%s]",
						$this->formatTime($prevtime + 3),
						$this->formatTime($line['ts'] - 3)
					) : "[{$this->formatTime($line['ts'])}]";
			}
			foreach ($line['l'] as $word) {
				$richsync .= "<{$this->formatTime($line['ts'] +$word['o'])}>{$word['c']}";
			}
			//Evade MiniLyrics bug
			$richsync .= "<{$this->formatTime($line['te'])}> \n";
			$prevtime = $line['te'];
			if ($idx === count($lrc) - 1)
				$richsync .= "[{$this->formatTime($line['te'] + 5)}]\n";
		}
		return $richsync;
	}
	public static function validateToken(): ?string
	{
		$token = env('MUSIXMATCH_TOKEN');
		abort_if(empty($token), 500, 'Musixmatch token was not found');
		return $token;
	}
	private static function matchError(mixed $ex): string
	{
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Musixmatch connection error {$ex->getCode()}: {$ex->getMessage()}",
			default => "Musixmatch unexpected error: {$ex->getMessage()}"
		};
	}
}
