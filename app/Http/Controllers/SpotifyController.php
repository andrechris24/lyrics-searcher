<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log, Session};
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use JsonException;
use SpotifyLyricsApi\Spotify;
use SpotifyLyricsApi\SpotifyException;

class SpotifyController extends Controller
{
	public function search(Request $req)
	{
		abort_if(empty(env('PAXSENIX_TOKEN')), 500, 'Paxsenix API token is required');
		try {
			$req->validate(['query' => 'required']);
			$r = Http::retry(2, 100)->timeout(25000)->withHeaders([
				'Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')
			])->get(parent::$paxsenix_url . 'spotify/search',	['q' => $req['query']])
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Spotify API error: ', $r);
				abort(
					500,
					'Oops, something went wrong while loading results. Please try again later.'
				);
			}
			return response()
				->json(['html' => view('spotify.list', ['data' => $r['tracks']])->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	public function get(string $id)
	{
		$spotify = new Spotify(env('SPOTIFY_COOKIE'));
		try {
			$spotify->checkTokenExpire();
			$lyrics = $spotify->getLyrics(track_id: $id);
			if(!empty($lyrics['lyrics']['lines'][0]['syllables'])) Log::debug($lyrics);
			return response()->json([
				'type'=>$lyrics['lyrics']['syncType'],
				'synced'=>$spotify->getLrcLyrics($lyrics['lyrics']['lines']),
				'srt'=>$spotify->getSrtLyrics($lyrics['lyrics']['lines']),
				'plain'=>$spotify->getRawLyrics($lyrics['lyrics']['lines']),
				'provider'=>$lyrics['lyrics']['providerDisplayName'],
				'id'=>$lyrics['lyrics']['providerLyricsId'],
				'syllable'=>$lyrics['lyrics']['lines'][0]['syllables']
			]);
		} catch (SpotifyException $e) {
			Log::error($e);
			abort($e->getCode(), $e->getMessage());
		}
		// try {
		// 	MusixmatchController::generateToken();
		// 	// Log::debug('Querying Spotify lyrics for id ' . $id);
		// 	$query = MusixmatchController::$macro_query;
		// 	$query['track_spotify_id'] = $id;
		// 	$query['usertoken'] = Session::get("mx_token");
		// 	$r = Http::retry(2, 5000, throw: false)->timeout(25000)
		// 		->withHeaders(MusixmatchController::MX_MACRO_HEADER)
		// 		->get(MusixmatchController::MX_MACRO_URL, $query)
		// 		->json(null, null, JSON_THROW_ON_ERROR);
		// 	// Log::debug($r);
		// 	$header = $r['message']['header'];
		// 	abort_if(
		// 		$header['status_code'] !== 200,
		// 		$header['status_code'],
		// 		'Error retrieving lyric: ' . parent::getMXerror($header)
		// 	);
		// 	$data = $r['message']['body']['macro_calls'];
		// 	$tmHeader = $data['matcher.track.get']['message']['header'];
		// 	abort_if(
		// 		$tmHeader['status_code'] !== 200,
		// 		$tmHeader['status_code'],
		// 		'Error retrieving lyric: ' . parent::getMXDBerror($tmHeader)
		// 	);
		// 	$tmBody = $data['matcher.track.get']['message']['body']['track'];
		// 	abort_if(
		// 		$tmBody['has_lyrics'] === 0 && $tmBody['has_subtitles'] === 0,
		// 		404,
		// 		"No lyric available for this song"
		// 	);
		// 	if ($tmBody['instrumental']) {
		// 		$syncedText = "[00:00.00]♪ Instrumental ♪";
		// 		$plainText = "♪ Instrumental ♪";
		// 	} else if ($tmBody['has_subtitles'] === 0) $syncedText = "";
		// 	else {
		// 		$syncedBody = $data['track.subtitles.get']['message']['body']['subtitle_list'][0]['subtitle'];
		// 		if ($syncedBody['restricted']) $syncedText = "";
		// 		else $syncedText = $syncedBody['subtitle_body'];
		// 	}
		// 	$plainBody = $data['track.lyrics.get']['message']['body']['lyrics'];
		// 	abort_if(
		// 		$plainBody['restricted'] === 1,
		// 		403,
		// 		"Lyric for this song is restricted"
		// 	);
		// 	if ($tmBody['instrumental'] === 0) $plainText = $plainBody['lyrics_body'];
		// 	return response()->json([
		// 		'share' => $tmBody['track_share_url'],
		// 		'release' => date_format(date_create($tmBody['first_release_date']), 'l, j F Y'),
		// 		'updated' => date_format(date_create($tmBody['updated_time']), 'l, j F Y'),
		// 		'copyright' => $plainBody['lyrics_copyright'],
		// 		'plain' => $plainText,
		// 		'synced' => $syncedText,
		// 		'richsync' => $tmBody['has_richsync'],
		// 		'track_id' => $tmBody['commontrack_id'],
		// 		'id' => $tmBody['subtitle_id'] ?? $id,
		// 		'instrumental' => $tmBody['instrumental']
		// 	]);
		// } catch (ConnectionException | JsonException $th) {
		// 	abort(500, MusixmatchController::matchMXError($th));
		// }
	}
	public function download(Request $req)
	{
		abort_if(empty(env('PAXSENIX_TOKEN')), 500, 'Paxsenix API token is required');
		$req->validate(
			['url' => 'required|url', 'source' => 'required|in:spotify,spotdl,youtube,deezer']
		);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])->get(
					parent::$paxsenix_url . 'dl/spotify',
					['url' => $req['url'], 'serv' => $req['source']]
				)->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Spotify API error: ', $r);
				abort(
					500,
					'Oops, something went wrong while downloading the song. Please try again later.'
				);
			}
			// Log::debug($r);
			return response()->json($r);
		} catch (ConnectionException | RequestException | JsonException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				parent::lyricallyError($e)
			);
		}
	}
	public function charts()
	{
		abort_if(empty(env('PAXSENIX_TOKEN')), 500, 'Paxsenix API token is required');
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get(parent::$paxsenix_url . 'spotify/charts')
				->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('Spotify API error: ', $r);
				return to_route('spotify.index')->withError(
					'Oops, something went wrong while loading charts. Please try again later.'
				);
			}
			return view('spotify.chart', $r);
		} catch (ConnectionException | RequestException | JsonException $e) {
			return to_route('spotify.index')
				->withError('Error loading charts: ' . parent::lyricallyError($e));
		}
	}
}
