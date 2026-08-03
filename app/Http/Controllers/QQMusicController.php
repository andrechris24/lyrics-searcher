<?php

namespace App\Http\Controllers;

use App\QrcDecoder;
use Illuminate\Http\Request;
use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

class QQMusicController extends Controller
{
	private const array QQ_HEADER = ["Referer" => "https://y.qq.com/"];
	private static string $url = 'https://c.y.qq.com/';
	public function search(Request $req)
	{
		try {
			$req->validate(['artist' => 'nullable', 'title' => 'required']);
			$response = Http::retry(3, 100)->timeout(25000)->withHeaders(self::QQ_HEADER)
				->get(self::$url . 'lyric/fcgi-bin/fcg_search_pc_lrc.fcg', [
					'SONGNAME' => $req['title'],
					'SINGERNAME' => $req['artist'],
					'TYPE' => 2,
					'RANGE_MIN' => 1,
					'RANGE_MAX' => 50
				]);
			libxml_use_internal_errors(true);
			$xmlResponse = simplexml_load_string(
				$response->body(),
				'SimpleXMLElement',
				LIBXML_NOCDATA
			);
			if ($xmlResponse === false) {
				$xmlErrors = libxml_get_errors();
				Log::error('Invalid XML response: ' . $response->body(), $xmlErrors);
				abort(500, 'Error parsing results: ' . json_encode(libxml_get_last_error()));
			}
			$xml = self::decodeJson(json_encode($xmlResponse)); //Convert XML Objects to Array
			abort_if(
				$xml === false,
				500,
				'Oops, an error occurred while reading QQ Music response.'
			);
			$data = $xml['cmd'];
			if (!in_array($data['result'], [0, 200])) {
				Log::error($data);
				abort(
					500,
					"Error loading results: QQ Music error {$data['result']}, {$data['reason']}"
				);
			}
			return response()->json(['html' => view('qqmusic.list', $data)->render()]);
		} catch (ConnectionException  | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::retry(3, 100)->timeout(25000)->withHeaders(self::QQ_HEADER)
				->get(self::$url . 'qqmusic/fcgi-bin/lyric_download.fcg', [
					'version' => 15,
					'miniversion' => 82,
					'lrctype' => 4,
					'musicid' => $id
				]);
			$res = Str::of($response->body())->remove('<!--')->remove('-->')
				->replaceMatches("/<miniversion.*\/>/", '');
			libxml_use_internal_errors(true);
			$xmlResponse = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
			if ($xmlResponse === false) {
				$xmlErrors = libxml_get_errors();
				Log::error("Invalid XML response: {$response->body()}", $xmlErrors);
				abort(500, 'Error parsing response: ' . libxml_get_last_error());
			}
			$xml = self::decodeJson(json_encode($xmlResponse));
			abort_if($xml === false, 500, 'Error reading QQ Music response');
			$data = $xml['cmd'];
			if (!in_array($data['result'], [0, 200])) {
				Log::error($data);
				abort(500, "QQ Music error {$data['result']}");
			}
			abort_if(
				empty($data['lyric']['content']),
				404,
				'No lyric available for this song entry'
			);
			if (ctype_xdigit($data['lyric']['content'])) {
				$decoder = new QrcDecoder();
				$lyricXml = $decoder->decode($data['lyric']['content']);
				$lyricXml = Str::between($lyricXml, 'LyricContent="', "\"/>\n");
				abort_if(empty($lyricXml), 404, 'Empty lyric, download aborted');
				$lyric =
					env('MINILYRICS_COMPATIBLE') ?
					Str::replace(">\n", "> \n", parent::qrcToLrc($lyricXml), false) :
					parent::qrcToLrc($lyricXml);
			} else {
				if (is_array($data['lyric']['content'])) {
					Log::error('Malformed lyric content: ', $data['lyric']['content']);
					abort(
						500,
						'Malformed lyric content. Wait for a while and try again. Contact site owner if issue persist.'
					);
				}
				$lyric = $data['lyric']['content'];
			}
			return response()->json([
				'lyric' => $lyric,
				'encoded' => ctype_xdigit($data['lyric']['content']),
				'id' => $id
			]);
		} catch (ConnectionException | RequestException $th) {
			abort(
				(get_class($th) === RequestException::class) ? $th->response->status() : 500,
				self::matchError($th)
			);
		}
	}
	private static function matchError(mixed $ex): string
	{
		if (get_class($ex) === RequestException::class) {
			Log::warning($ex->response->effectiveUri());
			if ($ex->response->status() !== 404) Log::error($ex);
		}
		return match (get_class($ex)) {
			ConnectionException::class => "QQ Music connection error {$ex->getCode()}: {$ex->getMessage()}",
			RequestException::class => "QQ Music HTTP Error {$ex->response->status()}",
			default => "QQ Music unexpected error: {$ex->getMessage()}"
		};
	}
}
