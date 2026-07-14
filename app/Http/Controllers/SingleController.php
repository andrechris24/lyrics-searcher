<?php

namespace App\Http\Controllers;

use App\Models\Lyric;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log, Session};
use JsonException;

class SingleController extends Controller
{
	public function search(Request $req)
	{
		$req->validate([
			'title' => 'required|string',
			'artist' => 'required|string',
			'album' => 'nullable|string',
			'source' => 'required|in:lrclib,musixmatch,plains,local'
		]);
		try {
			switch ($req['source']) {
				case 'lrclib':
					$param = ['artist_name' => $req['artist'], 'track_name' => $req['title']];
					if (!empty($req['album'])) $param['album_name'] = $req['album'];
					$response = Http::retry(3, 100, throw: false)->timeout(25000)
						->get('https://lrclib.net/api/get', $param);
					$r = $response->json(null, null, JSON_THROW_ON_ERROR);
					abort_if(
						$response->failed(),
						$response->status(),
						array_key_exists('message', $r)
							? $r['message']
							: "LRCLib HTTP Error {$response->status()}"
					);
					return response()->json([
						'title' => $r['trackName'],
						'artist' => $r['artistName'],
						'album' => $r['albumName'],
						'duration' => gmdate('i:s', $r['duration']),
						'plain' => $r['plainLyrics'],
						'synced' => $r['syncedLyrics'],
						'wbw' => $r['lyricsfile'],
						'instrumental' => $r['instrumental'],
						'id' => $r['id'],
						'source' => 'lrclib'
					]);
				case 'musixmatch':
					MusixmatchController::generateToken();
					$query = MusixmatchController::$macro_query;
					$query['q_album'] = $req['album'];
					$query['q_artist'] = $req['artist'];
					$query['q_track'] = $req['title'];
					$query['usertoken'] = Session::get("mx_token");
					$response = Http::retry(2, 5000, throw: false)->timeout(25000)
						->withHeaders(MusixmatchController::MX_MACRO_HEADER)
						->get(MusixmatchController::MX_MACRO_URL, $query);
					$r = $response->json(null, null, JSON_THROW_ON_ERROR);
					$header = $r['message']['header'];
					abort_if(
						$header['status_code'] !== 200,
						$header['status_code'],
						parent::getMXerror($header)
					);
					$data = $r['message']['body']['macro_calls'];
					$tmHeader = $data['matcher.track.get']['message']['header'];
					abort_if(
						$tmHeader['status_code'] !== 200,
						$tmHeader['status_code'],
						parent::getMXDBerror($tmHeader)
					);
					$tmBody = $data['matcher.track.get']['message']['body']['track'];
					$duration = $tmBody['track_length'];
					abort_if(
						$tmBody['has_lyrics'] === 0 && $tmBody['has_subtitles'] === 0,
						404,
						"Found song {$tmBody['artist_name']} - {$tmBody['track_name']} but no lyric available"
					);
					if ($tmBody['instrumental']) {
						$syncedText = "[00:00.00]♪ Instrumental ♪";
						$plainText = "♪ Instrumental ♪";
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
						"Lyric for song {$tmBody['artist_name']} - {$tmBody['track_name']} is restricted"
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
						'richsync' => $tmBody['has_richsync'],
						'track_id' => $tmBody['commontrack_id'],
						'id' => $tmBody['subtitle_id'],
						'instrumental' => $tmBody['instrumental'],
						'explicit' => $tmBody['explicit'],
						'source' => 'musixmatch'
					]);
				case 'plains':
					$ovhuri = sprintf(
						'https://api.lyrics.ovh/v1/%s/%s',
						urlencode($req['artist']),
						urlencode($req['title'])
					);
					$response = Http::retry(3, 100, throw: false)->timeout(25000)->get($ovhuri);
					$r = $response->json(null, null, JSON_THROW_ON_ERROR);
					if ($response->successful()) {
						return response()->json([
							'title' => $req['title'],
							'artist' => $req['artist'],
							'content' => $r['lyrics'],
							'instrumental' => false,
							'source' => 'lyrics.ovh'
						]);
					} else if (array_key_exists('error', $r)) {
						Log::warning($r);
						abort($response->status(), $r['error']);
					} else {
						Log::error('Unknown Lyrics.ovh response: ', $r);
						abort(500, 'Unknown response received');
					}
					break;
				case 'local':
					$model = Lyric::whereLike('title', "%{$req['title']}%")
						->whereLike('artist', "%{$req['artist']}%");
					if (!empty($req['album']))
						$model->whereLike('album', "%{$req['album']}%");
					$data = $model->firstOrFail();
					$data['user'] = $data->user;
					$data['source'] = 'local';
					$data['instrumental'] = false;
					return response()->json($data);
				default:
					abort(422, 'Unsupported source');
					break;
			}
		} catch (ConnectionException | JsonException | QueryException $th) {
			Log::error($th);
			$message = match (get_class($th)) {
				JsonException::class => "Error parsing response: {$th->getMessage()}",
				ConnectionException::class => "Connection error {$th->getCode()}: {$th->getMessage()}",
				QueryException::class => "Local database Error: {$th->errorInfo[2]}",
				default => "Unexpected error: {$th->getMessage()}"
			};
			abort(500, $message);
		}
	}
}
