<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log, Session};
use Illuminate\Validation\ValidationException;
use JsonException;
use Stevebauman\Location\Facades\Location;

class MusixmatchController extends Controller
{
	private const array MX_HEADER = ["cookie" => "AWSELBCORS=0; AWSELB=0"];
	public const MX_MACRO_HEADER =
	['authority' => 'apic-desktop.musixmatch.com', 'cookie' => 'x-mxm-token-guid='];
	public const MX_MACRO_URL = 'https://apic-desktop.musixmatch.com/ws/1.1/macro.subtitles.get';
	public static string $url = 'https://apic-desktop.musixmatch.com/ws/1.1/';
	private static array $query = [
		'user_language' => 'en',
		'app_id' => 'web-desktop-app-v1.0',
		'page_size' => 20,
		'f_has_lyrics' => 1
	];
	public static array $macro_query = [
		'format' => 'json',
		'namespace' => 'lyrics_richsynched',
		'app_id' => 'web-desktop-app-v1.0'
	];
	public function standard(Request $req)
	{
		try {
			$req->validate([
				'query' => 'required',
				'type' => 'required|in:all,track,artist,lyrics,track_artist,writer',
				'page' => 'nullable|integer|min:1'
			]);
			self::generateToken();
			$query = self::$query;
			$query['usertoken'] = Session::get('mx_token');
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
			$r = Http::retry(2, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)->get(self::$url . 'track.search', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.index')->withInput()
					->withError('Error loading search results: ' . parent::getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.result', compact('data', 'header'));
		} catch (ValidationException $e) {
			return to_route('musixmatch.index')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | \Exception $th) {
			return to_route('musixmatch.index')->withInput()->withErrors(self::matchError($th));
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
			self::generateToken();
			$query = self::$query;
			$query['usertoken'] = Session::get('mx_token');
			$query['q_track'] = $req['title'];
			$query['q_artist'] = $req['artist'];
			$query['q_lyrics'] = $req['lyrics'];
			$query['page'] = $req['page'] ?? 1;
			$r = Http::retry(2, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)->get(self::$url . 'track.search', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.advanced')->withInput()
					->withError('Error loading search results: ' . parent::getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.advanced.result', compact('data', 'header'));
		} catch (ValidationException $e) {
			return to_route('musixmatch.advanced')->withInput()->withErrors($e->errors());
		} catch (ConnectionException | JsonException | \Exception $th) {
			return to_route('musixmatch.advanced')->withInput()->withError(self::matchError($th));
		}
	}
	public function charts(string $type)
	{
		if (!in_array($type, ['top', 'hot', 'mxmweekly', 'mxmweekly_new'])) {
			return to_route('musixmatch.index')
				->withError('Unrecognized parameter for Musixmatch Chart');
		}
		self::generateToken();
		$query = self::$query;
		$query['usertoken'] = Session::get('mx_token');
		$query['chart_name'] = $type;
		$typeName = match ($type) {
			'top' => 'Top Charts',
			'hot' => 'Popular Lyrics',
			'mxmweekly' => 'Weekly Charts',
			'mxmweekly_new' => 'New Releases',
			default => null
		};
		if ($position = Location::get()) $query['country'] = $position->countryCode;
		else if ($type !== 'top') $query['country'] = 'xw';
		try {
			$r = Http::retry(2, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)->get(self::$url . 'chart.tracks.get', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			if ($header['status_code'] !== 200) {
				return to_route('musixmatch.index')
					->withError('Error loading charts: ' . parent::getMXerror($header));
			}
			$data = $r['message']['body']['track_list'];
			return view('musixmatch.chart', compact('data', 'header', 'position', 'typeName'));
		} catch (ValidationException $e) {
			return to_route('musixmatch.index')->withErrors($e->errors());
		} catch (ConnectionException | JsonException | \Exception $th) {
			return to_route('musixmatch.index')->withError(self::matchError($th));
		}
	}
	public function get(int $id, string $type)
	{
		abort_if(
			!in_array($type, ['subtitle', 'richsync', 'lyrics']),
			400,
			'Invalid lyric type request'
		);
		self::generateToken();
		$query = self::$query;
		$query['usertoken'] = Session::get('mx_token');
		$query['commontrack_id'] = $id;
		unset($query['f_has_lyrics'], $query['page_size']);
		try {
			$r = Http::retry(2, 5000, throw: false)->timeout(25000)
				->withHeaders(self::MX_HEADER)
				->get(self::$url . 'track.' . $type . '.get', $query)
				->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			abort_if(
				$header['status_code'] !== 200,
				$header['status_code'],
				parent::getMXerror($header)
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
					'content' => self::richsync(parent::decodeJson($data['richsync_body'])),
					'id' => $data['richsync_id'],
					'duration' => gmdate('i:s', $data['richsync_length'])
				],
				default => ['content' => $data['lyrics_body']]
			};
			return response()->json($lyrics);
		} catch (ConnectionException | JsonException $th) {
			abort(500, self::matchError($th));
		}
	}
	private static function richsync(array $lrc): ?string
	{
		if (empty($lrc) || $lrc === false) return null;
		$richsync = '';
		$prevtime = 0;
		foreach ($lrc as $idx => $line) {
			if ($idx === 0) {
				$richsync .= ($line['ts'] > 3) ?
					sprintf("[%s]", parent::formatTime($line['ts'] - 3)) :
					"[00:00.00]";
			} else {
				$richsync .= (($line['ts'] - $prevtime) > 9)
					? sprintf(
						"[%s]\n[%s]",
						parent::formatTime($prevtime + 3),
						parent::formatTime($line['ts'] - 3)
					) : sprintf("[%s]", parent::formatTime($line['ts']));
			}
			foreach ($line['l'] as $word) {
				$richsync .= sprintf(
					"<%s>%s",
					parent::formatTime($line['ts'] + $word['o']),
					$word['c']
				);
			}
			//Evade MiniLyrics bug
			$richsync .= sprintf("<%s> \n", parent::formatTime($line['te']));
			$prevtime = $line['te'];
			if ($idx === count($lrc) - 1)
				$richsync .= sprintf("[%s]\n", parent::formatTime($line['te'] + 5));
		}
		return $richsync;
	}
	public static function generateToken(): void
	{
		if (!Session::has('mx_token')) {
			$r = Http::retry(2, 5000, throw: false)->timeout(25000)
				->get('https://apic-desktop.musixmatch.com/ws/1.1/token.get', [
					'user_language' => 'en',
					'app_id' => 'web-desktop-app-v1.0'
				])->json(null, null, JSON_THROW_ON_ERROR);
			$header = $r['message']['header'];
			abort_if(
				$header['status_code'] !== 200,
				$header['status_code'],
				parent::getMXerror($header)
			);
			$body = $r['message']['body'];
			if (array_key_exists('user_token', $body)) {
				if ($body['user_token'] === 'UpgradeOnlyUpgradeOnlyUpgradeOnlyUpgradeOnly') {
					abort_if(
						empty(env('MUSIXMATCH_TOKEN')),
						500,
						'Failed to retrieve Musixmatch token, no fallback token found'
					);
					Session::put('mx_token', env('MUSIXMATCH_TOKEN'));
				} else Session::put('mx_token', $body['user_token']);
			} else {
				abort_if(
					empty(env('MUSIXMATCH_TOKEN')),
					500,
					'Failed to generate Musixmatch token, no fallback token found'
				);
				Session::put('mx_token', env('MUSIXMATCH_TOKEN'));
			}
		}
	}
	private static function matchError(mixed $ex): string
	{
		Log::error($ex);
		return match (get_class($ex)) {
			JsonException::class => "Error parsing response: {$ex->getMessage()}",
			ConnectionException::class => "Musixmatch connection error {$ex->getCode()}: {$ex->getMessage()}",
			default => "Musixmatch unexpected error: {$ex->getMessage()}"
		};
	}
}
