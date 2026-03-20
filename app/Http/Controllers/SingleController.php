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
				$r = json_decode($response->body());
				if (json_last_error() !== JSON_ERROR_NONE) {
					Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
					return response()->json([
						'message' => 'Error parsing JSON response: ' . json_last_error_msg(),
						'source' => 'LRCLib'
					], 500);
				} else if ($response->successful()) {
					$data = [
						'title' => $r->trackName,
						'artist' => $r->artistName,
						'album' => $r->albumName,
						'duration' => gmdate('i:s', $r->duration),
						'plain' => $r->plainLyrics,
						'synced' => $r->syncedLyrics,
						'instrumental' => $r->instrumental,
						'source' => 'lrclib'
					];
					return response()->json($data);
				}
				return response()->json(
					['message' => $r->message, 'source' => 'LRCLib'],
					$r->statusCode
				);
			} else if ($req['source'] === 'musixmatch') {
				if (empty(env('MUSIXMATCH_TOKEN'))) {
					return response()->json([
						'message' => 'Musixmatch token was not found'
					], 500);
				}
				Sleep::for(5)->seconds();
				$response = Http::withHeaders([
					"authority" => "apic-desktop.musixmatch.com",
					"cookie" => "x-mxm-token-guid="
				])->get('https://apic-desktop.musixmatch.com/ws/1.1/macro.subtitles.get', [
					'format' => 'json',
					'namespace' => 'lyrics_richsynched',
					'subtitle_format' => 'mxm',
					'app_id' => 'web-desktop-app-v1.0',
					'q_album' => $req['album'],
					'q_artist' => $req['artist'],
					'q_track' => $req['title'],
					'usertoken' => env('MUSIXMATCH_TOKEN')
				]);
				$r = json_decode($response->body(), true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					Log::error($response->body() . ' is not a valid JSON response, reason: ' . json_last_error_msg());
					return response()->json([
						'message' => 'Error parsing JSON response: ' . json_last_error_msg(),
						'source' => 'Musixmatch'
					], 500);
				}
				$header = $r['message']['header'];
				$data = $r['message']['body']['macro_calls'];
				if ($header['status_code'] !== 200) {
					return response()->json(
						[
							'message' => $this->getMXerror($header),
							'reason' => $header['hint'],
							'source' => 'Musixmatch'
						],
						$header['status_code']
					);
				}
				$tmHeader = $data['matcher.track.get']['message']['header'];
				if ($tmHeader['status_code'] !== 200) {
					if ($tmHeader['status_code'] === 404)
						$msg = "Song does not exist on database";
					else if ($tmHeader['status_code'] === 401)
						$msg = "Too many requests. Wait for a few minutes, then try again.";
					else if ($tmHeader['status_code'] === 400)
						$msg = "Invalid input, please make sure all * fields is filled.";
					else $msg = "Database HTTP Error " . $tmHeader['status_code'];
					return response()->json(
						['message' => $msg, 'source' => 'Musixmatch'],
						$tmHeader['status_code']
					);
				}
				$tmBody = $data['matcher.track.get']['message']['body']['track'];
				if ($tmBody['instrumental']) {
					$syncedText = "[00:00.00]♪ Instrumental ♪";
					$plainText = "♪ Instrumental ♪";
				} else if ($tmBody['has_lyrics'] === 0 && $tmBody['has_subtitles'] === 0) {
					return response()->json([
						'message' => "Found song " . $tmBody['artist_name'] . ' - ' . $tmBody['track_name'] . " but no lyric available",
						'source' => 'Musixmatch'
					], 404);
				} else if ($tmBody['has_subtitles'] === 0) $syncedText = "";
				else {
					$syncedBody = $data['track.subtitles.get']['message']['body']['subtitle_list'][0]['subtitle'];
					if ($syncedBody['restricted']) $syncedText = "";
					else $syncedText = $this->synced(json_decode($syncedBody['subtitle_body']));
				}
				$plainBody = $data['track.lyrics.get']['message']['body']['lyrics'];
				if ($plainBody['restricted']) {
					return response()->json([
						'message' => "Lyric for this song is restricted",
						'source' => 'Musixmatch'
					], 403);
				} else if ($tmBody['instrumental'] === 0)
					$plainText = $plainBody['lyrics_body'];
				$data = [
					'title' => $tmBody['track_name'],
					'artist' => $tmBody['artist_name'],
					'album' => $tmBody['album_name'],
					'art100' => $tmBody['album_coverart_100x100'],
					'art350' => $tmBody['album_coverart_350x350'],
					'art500' => $tmBody['album_coverart_500x500'],
					'art800' => $tmBody['album_coverart_800x800'],
					'duration' => gmdate('i:s', $tmBody['track_length']),
					'spotify' => $tmBody['track_spotify_id'],
					'share' => $tmBody['track_share_url'],
					'release' => date_format(date_create($tmBody['first_release_date']), 'l, j F Y'),
					'updated' => date_format(date_create($tmBody['updated_time']), 'l, j F Y'),
					'copyright' => $plainBody['lyrics_copyright'],
					'plain' => $plainText,
					'synced' => $syncedText,
					'instrumental' => $tmBody['instrumental'],
					'source' => 'musixmatch'
				];
				return response()->json($data);
			}
		} catch (ConnectionException $e) {
			Log::error($e);
			return response()->json([
				'message' => 'Request failed: ' . $e->getMessage()
			], 500);
		}
		return response()->json(['message' => 'Unsupported source'], 400);
	}
	private function synced($lrc)
	{ //For Musixmatch only
		$synced = '';
		foreach ($lrc as $line) {
			$time = $line->time;
			$synced .= "[" . gmdate('i:s', $time->total) . '.' . sprintf('%02d', $time->hundredths) . ']';
			$synced .= $line->text . "\n";
		}
		return $synced;
	}
}
