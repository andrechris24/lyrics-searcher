<?php

namespace App\Http\Controllers;

use App\QrcDecoder;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QQMusicController extends Controller
{
	public const array QQ_HEADER = ["Referer" => "https://y.qq.com/"];
	public static string $url = 'https://c.y.qq.com/';
	public function search(Request $req)
	{
		try {
			$req->validate([
				'artist' => 'nullable',
				'title' => 'required',
				'offset' => 'nullable|integer|min:0|multiple_of:20'
			]);
			$response = Http::connectTimeout(30)->withHeaders(self::QQ_HEADER)
				->get(self::$url . 'lyric/fcgi-bin/fcg_search_pc_lrc.fcg', [
					'SONGNAME' => $req['title'],
					'SINGERNAME' => $req['artist'],
					'TYPE' => 2,
					'RANGE_MIN' => ($req['offset'] ?? 0) + 1,
					'RANGE_MAX' => 20 + ($req['offset'] ?? 0)
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
				return to_route('qqmusic.index')->withInput()
					->withError('Error parsing response: ' . libxml_get_last_error());
			}
			$xml = json_decode(json_encode($xmlResponse), true);
			// dd($xml);
			$data = $xml['cmd'];
			if (!in_array($data['result'], [0, 200])) {
				Log::error($data);
				return to_route('qqmusic.index')->withInput()
					->withError('QQ Music error ' . $data['result'] . ', ' . $data['reason']);
			}
			return view('qqmusic.result', $data);
		} catch (ConnectionException $th) {
			Log::error($th);
			return to_route('qqmusic.index')->withInput()
				->withError('QQ Music connection failed: ' . $th->getMessage());
		} catch (ValidationException $e) {
			return to_route('qqmusic.index')->withInput()->withErrors($e->errors());
		}
	}
	public function get(int $id)
	{
		try {
			$response = Http::connectTimeout(30)->withHeaders(self::QQ_HEADER)
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
				Log::error('Invalid XML response: ' . $response->body(), $xmlErrors);
				abort(500, 'Error parsing response: ' . libxml_get_last_error());
			}
			$xml = json_decode(json_encode($xmlResponse), true);
			$data = $xml['cmd'];
			if (!in_array($data['result'], [0, 200])) {
				Log::error($data);
				abort(500, 'QQ Music error ' . $data['result']);
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
				$lyric = $this->qrcToLrc($lyricXml);
			} else {
				if (is_array($data['lyric']['content'])) {
					Log::error('Malformed lyric content: ', $data['lyric']['content']);
					// Log::notice('Full response: ',$data);
					abort(500, 'Malformed lyric content. Wait for a while and try again.');
				}
				$lyric = $data['lyric']['content'];
			}
			return response()->json([
				'lyric' => $lyric,
				'encoded' => ctype_xdigit($data['lyric']['content']),
				'id' => $id
			]);
		} catch (ConnectionException $th) {
			Log::error($th);
			abort(500, 'QQ Music connection failed: ' . $th->getMessage());
		}
	}
}
