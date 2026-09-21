<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use JsonException;
use SpotifyLyricsApi\Spotify;
use SpotifyLyricsApi\SpotifyException;

class SpotifyController extends Controller
{
	public function search(Request $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API token is required for Spotify requests'
		);
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
				'Error loading results: ' . parent::lyricallyError($th)
			);
		}
	}
	public function get(string $id)
	{
		$spotify = new Spotify(env('SPOTIFY_COOKIE'));
		try {
			$spotify->checkTokenExpire();
			$lyrics = $spotify->getLyrics(track_id: $id);
			if (!empty($lyrics['lyrics']['lines'][0]['syllables']))
				Log::debug($lyrics['lyrics']);
			return response()->json([
				'type' => $lyrics['lyrics']['syncType'],
				'synced' => $spotify->getLrcLyrics($lyrics['lyrics']['lines']),
				'srt' => $spotify->getSrtLyrics($lyrics['lyrics']['lines']),
				'plain' => $spotify->getRawLyrics($lyrics['lyrics']['lines']),
				'provider' => $lyrics['lyrics']['providerDisplayName'],
				'id' => $lyrics['lyrics']['providerLyricsId'],
				'syllable' => $lyrics['lyrics']['lines'][0]['syllables']
			]);
		} catch (SpotifyException $e) {
			Log::error($e);
			abort($e->getCode(), 'Error retrieving lyric: ' . $e->getMessage());
		}
	}
	public function download(Request $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API token is required for Spotify requests'
		);
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
					'Oops, something went wrong while downloading song. Please try again later.'
				);
			}
			// Log::debug($r);
			return response()->json($r);
		} catch (ConnectionException | RequestException | JsonException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'Download failed: ' . parent::lyricallyError($e)
			);
		}
	}
	public function charts()
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API token is required for Spotify requests'
		);
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
