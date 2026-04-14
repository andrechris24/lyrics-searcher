<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Sleep;

class SingleController extends Controller
{
	public function search(Request $req)
	{
		$req->validate([
			'title' => 'required|string',
			'artist' => 'required|string',
			'album' => 'nullable|string',
			'source' => 'required|in:lrclib,musixmatch'
		]);
		try {
			if ($req->source === 'lrclib') {
				$param = ['artist_name' => $req['artist'], 'track_name' => $req['title']];
				if (!empty($req['album'])) $param['album_name'] = $req['album'];
				$response = Http::get('https://lrclib.net/api/get', $param);
				$r = self::decodeJson($response->body());
				abort_if($r === false, 500, 'Error parsing JSON response: ' . json_last_error_msg());
				if ($response->successful()) {
					return response()->json([
						'title' => $r['trackName'],
						'artist' => $r['artistName'],
						'album' => $r['albumName'],
						'duration' => gmdate('i:s', $r['duration']),
						'plain' => $r['plainLyrics'],
						'synced' => $r['syncedLyrics'],
						'instrumental' => $r['instrumental'],
						'source' => 'lrclib'
					]);
				}
				abort($r['statusCode'], $r['message']);
			} else if ($req['source'] === 'musixmatch') {
				abort_if(empty(env('MUSIXMATCH_TOKEN')), 500, 'Musixmatch token was not found');
				Sleep::for(5)->seconds();
				$response = Http::withHeaders([
					"authority" => "apic-desktop.musixmatch.com",
					"cookie" => "x-mxm-token-guid="
				])->get('https://apic-desktop.musixmatch.com/ws/1.1/macro.subtitles.get', [
					'format' => 'json',
					'namespace' => 'lyrics_richsynched',
					'app_id' => 'web-desktop-app-v1.0',
					'q_album' => $req['album'],
					'q_artist' => $req['artist'],
					'q_track' => $req['title'],
					'usertoken' => env('MUSIXMATCH_TOKEN')
				]);
				$r = self::decodeJson($response->body());
				abort_if($r === false, 500, 'Error parsing JSON response: ' . json_last_error_msg());
				$header = $r['message']['header'];
				abort_if(
					$header['status_code'] !== 200,
					$header['status_code'],
					$this->getMXerror($header)
				);
				$data = $r['message']['body']['macro_calls'];
				$tmHeader = $data['matcher.track.get']['message']['header'];
				if ($tmHeader['status_code'] !== 200) {
					$msg = match ($tmHeader['status_code']) {
						404 => "Song does not exist on database",
						401 => "Too many requests. Wait for a few minutes, then try again.",
						400 => "Invalid input, please make sure all * fields is filled.",
						default => "Database HTTP Error " . $tmHeader['status_code'],
					};
					abort($tmHeader['status_code'], $msg);
				}
				$tmBody = $data['matcher.track.get']['message']['body']['track'];
				$duration = $tmBody['track_length'];
				if ($tmBody['instrumental']) {
					$syncedText = "[00:00.00]♪ Instrumental ♪";
					$plainText = "♪ Instrumental ♪";
				} else if ($tmBody['has_lyrics'] === 0 && $tmBody['has_subtitles'] === 0) {
					abort(
						404,
						"Found song " . $tmBody['artist_name'] . ' - ' . $tmBody['track_name'] . " but no lyric available"
					);
				} else if ($tmBody['has_subtitles'] === 0) $syncedText = "";
				else {
					$syncedBody = $data['track.subtitles.get']['message']['body']['subtitle_list'][0]['subtitle'];
					if ($syncedBody['restricted']) $syncedText = "";
					else {
						$syncedText = $syncedBody['subtitle_body'];
						$duration = $syncedBody['subtitle_length'];
					}
				}
				$plainBody = $data['track.lyrics.get']['message']['body']['lyrics'];
				abort_if(
					$plainBody['restricted'] === 1,
					403,
					"Lyric for song " . $tmBody['artist_name'] . ' - ' . $tmBody['track_name'] . "is restricted"
				);
				if ($tmBody['instrumental'] === 0) $plainText = $plainBody['lyrics_body'];
				return response()->json([
					'title' => $tmBody['track_name'],
					'artist' => $tmBody['artist_name'],
					'album' => $tmBody['album_name'],
					'art100' => $tmBody['album_coverart_100x100'],
					'art350' => $tmBody['album_coverart_350x350'],
					'art500' => $tmBody['album_coverart_500x500'],
					'art800' => $tmBody['album_coverart_800x800'],
					'duration' => gmdate('i:s', $duration),
					'spotify' => $tmBody['track_spotify_id'],
					'share' => $tmBody['track_share_url'],
					'release' => date_format(date_create($tmBody['first_release_date']), 'l, j F Y'),
					'updated' => date_format(date_create($tmBody['updated_time']), 'l, j F Y'),
					'copyright' => $plainBody['lyrics_copyright'],
					'plain' => $plainText,
					'synced' => $syncedText,
					'instrumental' => $tmBody['instrumental'],
					'source' => 'musixmatch'
				]);
			}
		} catch (ConnectionException $e) {
			Log::error($e);
			abort(500, 'Connection failed: ' . $e->getMessage());
		}
		abort(400, 'Unsupported source');
	}
	public function wideSearch(Request $req)
	{
		$req->validate(['keyword' => 'required']);
		$data = $error = [];
		try {
			// TODO: Provider-wide search
		} catch (ConnectionException $e) {
			Log::error($e);
			$error[] = "Connection failed: " . $e->getMessage();
		}
		return view('search', compact('data', 'error'));
	}
}
