<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Sleep;
use JsonException;

class YoutubeController extends Controller
{
	public function search(Request $req)
	{
		$req->validate(['query' => 'required']);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . 'youtube/search', ['q' => $req['query']])->json(null, null, JSON_THROW_ON_ERROR);
			if (array_key_exists('error', $r)) {
				Log::error('YouTube API error: ', $r);
				abort(
					500,
					'Oops, something went wrong while loading results. Please try again later.'
				);
			}
			return response()
				->json(['html' => view('youtube.list', ['data' => $r])->render()]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	public function get(string $id)
	{
		try {
			$response = Http::retry(2, 100)->timeout(25000)
				->get(parent::$paxsenix_url . 'youtube/lyrics', ['id' => $id]);
			abort_if(
				empty($response->body()) || $response->body() === '""',
				404,
				'No lyric available for this song'
			);
			$r = $response->json(null, null, JSON_THROW_ON_ERROR);
			if (is_array($r)) {
				if (array_key_exists('isError', $r) && $r['isError'] === true) {
					abort_if($r['error'] === 'No lyrics found', 404, 'No lyric available for this song');
					Log::error('YouTube API error: ', $r);
					abort(500, $r['error']);
				} else {
					Log::error('Malformed lyric content from YouTube: ', $r);
					abort(500, 'Malformed lyric content, please contact site owner.');
				}
			} else if (empty($r) || $r === false) $r = $response->body();
			return response()->json(['lyric' => $r, 'id' => $id]);
		} catch (ConnectionException | JsonException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				parent::lyricallyError($th)
			);
		}
	}
	public function dlvideo(Request $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API token is required for YouTube downloads'
		);
		$req->validate(
			['url' => 'required|url', 'q' => 'required|in:360,480,720,1080,1440']
		);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get(
					parent::$paxsenix_url . 'dl/ytmp4',
					['url' => $req['url'], 'quality' => $req['q']]
				)->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('YouTube API error: ', $r);
				abort(500, 'Failed to retrieve YouTube Link. Please try again later.');
			}
			do {
				$queue = Http::timeout(25000)->get($r['task_url']);
				$arrQueue = $queue->json(null, null, JSON_THROW_ON_ERROR);
				if ($r['ok'] === false && !array_key_exists('status', $arrQueue)) {
					Log::error($arrQueue);
					abort(
						$queue->status(),
						$arrQueue['message'] ?? "Unknown error while downloading video"
					);
				} else if (in_array($arrQueue['status'], ['failed', 'error'])) {
					Log::error('Error downloading YouTube Video: ', $arrQueue);
					abort(
						500,
						$arrQueue['error'] ?? $arrQueue['message'] ?? 'Failed to download YouTube Video'
					);
					break;
				} else if ($arrQueue['status'] === 'pending')
					Sleep::for(5)->seconds();
			} while ($arrQueue['status'] === 'pending');
			return response()
				->json(['html' => view('youtube.result', $arrQueue)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			// if (get_class($e) === RequestException::class) {
			// 	$json = $e->response->json();
			// 	abort_if(
			// 		is_array($json) && array_key_exists('qualities', $json),
			// 		400,
			// 		'Selected quality is unavailable. Available qualities: ' . implode(', ', $json['qualities'])
			// 	);
			// }
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'YouTube Video download failed: ' . parent::lyricallyError($e)
			);
		}
	}
	public function dlaudio(Request $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API token is required for YouTube downloads'
		);
		$req->validate(
			['url' => 'required|url', 'fmt' => 'required|in:mp3,m4a,webm,aac,flac,opus,ogg,wav']
		);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get(
					parent::$paxsenix_url . 'dl/ytmp3',
					['url' => $req['url'], 'format' => $req['fmt']]
				)->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('YouTube API error: ', $r);
				abort(500, 'Failed to retrieve YouTube link. Please try again later.');
			}
			do {
				$queue = Http::timeout(25000)->get($r['task_url']);
				$arrQueue = $queue->json(null, null, JSON_THROW_ON_ERROR);
				if ($r['ok'] === false && !array_key_exists('status', $arrQueue)) {
					Log::error($arrQueue);
					abort(
						$queue->status(),
						$arrQueue['message'] ?? "Unknown error while downloading audio"
					);
				} else if (in_array($arrQueue['status'], ['failed', 'error'])) {
					Log::error('Error downloading YouTube Audio: ', $arrQueue);
					abort(
						500,
						$arrQueue['error'] ?? $arrQueue['message'] ?? 'Failed to download YouTube Audio'
					);
					break;
				} else if ($arrQueue['status'] === 'pending')
					Sleep::for(5)->seconds();
			} while ($arrQueue['status'] === 'pending');
			return response()
				->json(['html' => view('youtube.result', $arrQueue)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'YouTube Audio download failed: ' . parent::lyricallyError($e)
			);
		}
	}
	public function altdl(Request $req)
	{
		abort_if(
			empty(env('PAXSENIX_TOKEN')),
			401,
			'API token is required for YouTube downloads'
		);
		$req->validate(
			['url' => 'required|url', 'fmt' => 'required|in:mp3,1080,720,480,360,240,144']
		);
		try {
			$r = Http::retry(2, 100)->timeout(25000)
				->withHeaders(['Authorization' => 'Bearer ' . env('PAXSENIX_TOKEN')])
				->get(
					parent::$paxsenix_url . 'yt/savetube',
					['url' => $req['url'], 'quality' => $req['fmt']]
				)->json(null, null, JSON_THROW_ON_ERROR);
			if ($r['ok'] === false) {
				Log::error('YouTube API error: ', $r);
				abort(500, 'Failed to retrieve YouTube link. Please try again later.');
			}
			do {
				$queue = Http::timeout(25000)->get($r['task_url']);
				$arrQueue = $queue->json(null, null, JSON_THROW_ON_ERROR);
				if ($r['ok'] === false && !array_key_exists('status', $arrQueue)) {
					Log::error($arrQueue);
					abort(
						$queue->status(),
						$arrQueue['message'] ?? "Unknown error while downloading YouTube Content"
					);
				} else if (in_array($arrQueue['status'], ['failed', 'error'])) {
					Log::error('Error downloading YouTube Content: ', $arrQueue);
					abort(
						500,
						$arrQueue['message'] ?? 'Failed to download YouTube Content'
					);
					break;
				} else if ($arrQueue['status'] === 'pending')
					Sleep::for(5)->seconds();
			} while ($arrQueue['status'] === 'pending');
			return response()
				->json(['html' => view('youtube.result', $arrQueue)->render()]);
		} catch (ConnectionException | JsonException | RequestException $e) {
			abort(
				(get_class($e) === RequestException::class) ? $e->response->status() : 500,
				'YouTube Content download failed: ' . parent::lyricallyError($e)
			);
		}
	}
}
