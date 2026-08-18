<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Session};
use Illuminate\Support\Str;
use JsonException;

abstract class Controller
{
	protected const APP_HEADER =
	['User-Agent' => 'LRCSearch/1.0 (https://github.com/andrechris24/lyrics-searcher)'];
	protected const PAXSENIX_TOKEN = ['Authorization' => 'Bearer sk-paxsenix-guD0ZfVcPQUB37l-d0StUItAtBNr3kxrZE549SfzpxSeVhgF'];
	protected static string $paxsenix_url = "https://api.paxsenix.org/";

	/**
	 * Get error messages from Musixmatch
	 *
	 * @param array $header
	 * @return string
	 */
	protected static function getMXerror(array $header): string
	{
		if ($header['status_code'] === 401) Session::forget('mx_token');
		if (array_key_exists('hint', $header)) {
			$msg = match ($header['hint']) {
				'renew' => "Musixmatch token expired or invalid. Please try again to regenerate token.",
				'captcha' => "Musixmatch blocked your IP",
				default => "Musixmatch returned an error with reason: {$header['hint']}"
			};
		} else {
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
				else if (in_array($matches[1], ['total'])) {
					$lyricText .= sprintf("[length: %s]\n", gmdate('i:s', floor($matches[2] / 1000)));
					continue;
				}
				$lyricText .= $matches[0] . "\n";
			} else if (preg_match($timestampsRegex, $line, $matches)) {
				$lyricLine = "";
				$startTime = (int)$matches[1];
				$duration = (int)$matches[2];
				if ($idx === 0) {
					$lyricLine .= ($startTime > 3000)
						? "[" . self::formatTime(($startTime - mt_rand(2500, 3000)) / 1000) . "]"
						: "[00:00.00]";
				} else if (($startTime - $prevtime) > 9000) {
					$lyricLine .= sprintf(
						"[%s]\n[%s]",
						self::formatTime(($prevtime + mt_rand(2500, 3500)) / 1000),
						self::formatTime(($startTime - mt_rand(2500, 3500)) / 1000)
					);
				} else $lyricLine .= sprintf("[%s]", self::formatTime($startTime / 1000));
				// parse sub-timestamps
				if (preg_match_all($timestamps2Regex, $line, $subMatches)) {
					for ($a = 0; $a < count($subMatches[0]); $a++) {
						$lyricLine .= sprintf(
							"<%s>%s",
							self::formatTime(($startTime + (int)$subMatches[1][$a]) / 1000),
							$subMatches[4][$a]
						);
					}
				}
				$prevtime = $startTime + $duration;
				$lyricText .= sprintf(
					env("MINILYRICS_COMPATIBLE", true) ? "%s<%s> \n" : "%s<%s>\n",
					$lyricLine,
					self::formatTime(($startTime + $duration) / 1000)
				);
				if ($idx === count($lines) - 1)
					$lyricText .= "[" . self::formatTime(($startTime + $duration + 1) / 1000) . "]";
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
				return sprintf("[%s]", self::formatTime((int)$matches[1] / 1000));
			})->replaceMatches("/\((\d+),(\d+)\)/", function (array $matches) use (&$sylTime) {
				$sylTime = self::formatTime(((int)$matches[1] + (int)$matches[2]) / 1000);
				return sprintf("<%s>", $sylTime);
			});
		$converted .= "\n[$sylTime]";
		return $converted;
	}

	protected static function lyricallyError(mixed $e)
	{
		if (get_class($e) === RequestException::class) {
			Log::warning('Request failed for ' . $e->response->effectiveUri());
			$json = $e->response->json();
			if (!$json) $reqerr = "Lyrically API Error {$e->response->status()}";
			else
				$reqerr = array_key_exists('message', $json) ? $json['message'] : $json['error'];
		}
		Log::error($e);
		return match (get_class($e)) {
			JsonException::class => "Error parsing Lyrically API response: {$e->getMessage()}",
			ConnectionException::class => "Lyrically API connection error {$e->getCode()}: {$e->getMessage()}",
			RequestException::class => $reqerr,
			default => "Lyrically API unexpected error: {$e->getMessage()}"
		};
	}
}
