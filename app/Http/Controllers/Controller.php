<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Session, Http};
use Illuminate\Support\Str;
use JsonException;

abstract class Controller
{
	protected const APP_HEADER =
	['User-Agent' => 'LRCSearch/1.1 (https://github.com/andrechris24/lyrics-searcher)'];
	protected static string $paxsenix_url = "https://api.paxsenix.org/";
	protected static string $lyrically_url = "https://lyrics.paxsenix.org/";

	/**
	 * Get error messages from Musixmatch
	 *
	 * @param array $header
	 * @return string
	 */
	protected static function getMXerror(array $header): string
	{
		if (array_key_exists('hint', $header)) {
			Session::forget('mx_token');
			$msg = match ($header['hint']) {
				'renew' => "Musixmatch token expired or invalid. Please try again to regenerate token.",
				'captcha' => "Musixmatch blocked your IP, please wait for a few minutes or refresh your device IP address.",
				default => "Musixmatch returned an error with reason: {$header['hint']}"
			};
		} else {
			if ($header['status_code'] === 401) Session::forget('mx_token');
			$msg = match ($header['status_code']) {
				401 => "Musixmatch rate limit exceeded. Please try again to regenerate token.",
				404 => "Musixmatch query returned no result",
				400 => "Bad request sent to Musixmatch. Please report this issue.",
				default => "Musixmatch HTTP Error {$header['status_code']}"
			};
		}
		return $msg;
	}

	protected static function getMXDBerror(array $tmHeader): string
	{
		if ($tmHeader['status_code'] === 401) Session::forget('mx_token');
		return match ($tmHeader['status_code']) {
			404 => "Song does not exist on Musixmatch database",
			401 => "Too many requests. Please try again to regenerate musixmatch token.",
			400 => "Invalid Musixmatch input, please report this issue.",
			default => "Musixmatch database HTTP Error {$tmHeader['status_code']}"
		};
	}

	/**
	 * Convert seconds (with decimals) to mm:ss.xx format
	 *
	 * @param int|float $seconds
	 * @return string
	 */
	protected static function formatTime(int|float $seconds, bool $milliseconds = false): string
	{
		if (!is_numeric($seconds) || $seconds < 0) {
			Log::warning("Invalid time value: $seconds");
			return '00:00.00';
		}

		if ($milliseconds === true)
			$seconds = $seconds / 1000;

		// Extract whole minutes
		$minutes = floor($seconds / 60);

		// Remaining seconds (with decimals)
		$remainingSeconds = $seconds - ($minutes * 60);

		// Format with leading zeros and 2 decimal places
		return sprintf("%02d:%05.2f", $minutes, $remainingSeconds);
	}

	/**
	 * Decodes JSON string
	 *
	 * @param  string $json
	 * @return array|false	Return decoded json in array, false on failure
	 */
	protected static function decodeJson(string $json): array|false
	{
		try {
			$res = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
			return $res;
		} catch (JsonException $e) {
			Log::error($e);
			return false;
		}
	}

	/**
	 * Converts KRC lyrics to Enhanced LRC
	 * @param  string $krcText
	 * @return string|null
	 */
	protected static function krc2lrc(string $krcText): ?string
	{
		if (empty($krcText)) return null;
		$lyricText = "";
		$metaRegex = "/^\[(\S+):(\S+)\]$/";
		$timestampsRegex = "/^\[(\d+),(\d+)\]/";
		$timestamps2Regex = "/<(\d+),(\d+),(\d+)>([^<]*)/";
		$lines = preg_split("/\r\n|\r|\n/", $krcText);
		$prevtime = 0;
		foreach ($lines as $idx => $line) {
			if (preg_match($metaRegex, $line, $matches)) { // meta info
				if (
					in_array($matches[1], ['language', 'sign', 'id']) ||
					(in_array($matches[1], ['ar', 'ti']) && is_numeric($matches[2]))
				) continue;
				else if ($matches[1] == 'total') {
					$lyricText .= sprintf(
						"[length:%s]\n",
						gmdate('i:s', floor($matches[2] / 1000))
					);
					continue;
				}
				$lyricText .= $matches[0] . PHP_EOL;
			} else if (preg_match($timestampsRegex, $line, $matches)) {
				$lyricLine = "";
				$startTime = (int)$matches[1];
				$duration = (int)$matches[2];
				if ($idx === 0) {
					$lyricLine .= ($startTime > 3000)
						? "[" . self::formatTime(($startTime - mt_rand(2500, 3000)), true) . "]"
						: "[00:00.00]";
				} else if (($startTime - $prevtime) > 9000) {
					$lyricLine .= sprintf(
						"[%s]\n[%s]",
						self::formatTime(($prevtime + mt_rand(2500, 3500)), true),
						self::formatTime(($startTime - mt_rand(2500, 3500)), true)
					);
				} else $lyricLine .= sprintf("[%s]", self::formatTime($startTime, true));
				// parse sub-timestamps
				if (preg_match_all($timestamps2Regex, $line, $subMatches)) {
					for ($a = 0; $a < count($subMatches[0]); $a++) {
						$lyricLine .= sprintf(
							"<%s>%s",
							self::formatTime(($startTime + (int)$subMatches[1][$a]), true),
							$subMatches[4][$a]
						);
					}
				}
				$prevtime = $startTime + $duration;
				$formattedTime = self::formatTime(($startTime + $duration), true);
				$lyricText .=
					env('MINILYRICS_COMPATIBLE', false)
					? sprintf("%s<%s> <%s>\n", $lyricLine, $formattedTime, $formattedTime)
					: sprintf("%s<%s>\n", $lyricLine, $formattedTime);
				if ($idx === count($lines) - 1)
					$lyricText .= "[" . self::formatTime(($startTime + $duration + 1), true) . "]";
			}
		}
		return $lyricText;
	}

	/**
	 * Converts decoded QRC lyrics to Enhanced LRC format
	 * @param  string $qrcText
	 * @return string|null
	 */
	protected static function qrcToLrc(string $qrcText): ?string
	{
		if (empty($qrcText)) return null;
		$sylTime = '';
		$converted = Str::of($qrcText)
			->replaceMatches("/^\[(\d+),(\d+)\]/m", function (array $matches) {
			return sprintf("[%s]", self::formatTime((int)$matches[1], true));
			})->replaceMatches("/\((\d+),(\d+)\)/", function (array $matches) use (&$sylTime) {
			$sylTime = self::formatTime(((int)$matches[1] + (int)$matches[2]), true);
				return sprintf("<%s>", $sylTime);
			});
		$converted .= "[$sylTime]";
		return env('MINILYRICS_COMPATIBLE', false) ?
			Str::replace(">\n", "> \n", $converted, false) :
			$converted;
	}

	protected static function lyricallyError(mixed $e, bool $lrc = false): string
	{
		if (get_class($e) === RequestException::class) {
			Log::warning('Request failed for ' . $e->response->effectiveUri());
			$json = $e->response->json();
			if (!$json) $reqerr = "Paxsenix API Error {$e->response->status()}";
			else if ($lrc === true && $e->response->status() === 404)
				$reqerr = 'No lyric found for this song';
			else
				$reqerr = $json['message'] ?? $json['error'] ?? $json['detail'] ?? "Paxsenix API Error {$e->response->status()}";
		}
		Log::error($e);
		return match (get_class($e)) {
			JsonException::class => "Malformed Paxsenix API response ({$e->getMessage()})",
			ConnectionException::class => "Paxsenix API connection error, {$e->getMessage()}",
			RequestException::class => $reqerr,
			default => "Paxsenix API unexpected error"
		};
	}

	protected static function getFileInfo(string $url): array
	{
		$response = Http::head($url);
		if ($response->successful())
			$mimeType = $response->header('Content-Type');
		else {
			Log::warning('Failed to get mime type for ' . $url . ': ', $response);
			$mimeType = 'audio/webm';
		}
		$fileInfo = pathinfo($url);
		return [
			'type' => $mimeType,
			'ext' => $fileInfo['extension'],
			'name' => $fileInfo['filename']
		];
	}

	/**
	 * Sets LRC by: tag to source of LRC file if by is unavailable
	 * @param string $lrc    Content of LRC file
	 * @param string $source Source of LRC file
	 */
	protected static function setLrcAuthor(string $lrc, string $source): string
	{
		if (!Str::contains($lrc, '[by:')) $lrc = "[by:$source]\n$lrc";
		return Str::replace('[by:]', "[by:$source]", $lrc);
	}
}
