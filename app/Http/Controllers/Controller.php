<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
	/**
	 * Get error messages from Musixmatch
	 *
	 * @param array $header
	 * @return string
	 */
	protected function getMXerror(array $header)
	{
		if (array_key_exists('hint', $header)) {
			$msg = match ($header['hint']) {
				'renew' => "Invalid Musixmatch token",
				'captcha' => "Musixmatch blocked your IP",
				default => "Musixmatch returned an error with reason: " . $header['hint']
			};
		} else {
			$msg = match ($header['status_code']) {
				401 => "Musixmatch rate limit exceeded. Please try again later.",
				404 => "Musixmatch query returned no result",
				400 => "Bad request sent to Musixmatch. Please report this issue.",
				500 => "Musixmatch server error. Please try again later.",
				503 => "Musixmatch service unavailable. Please try again later.",
				default => "Musixmatch HTTP Error " . $header['status_code']
			};
		}
		return $msg;
	}

	/**
	 * Convert seconds (with decimals) to mm:ss.xx format
	 *
	 * @param int|float $seconds
	 * @return string
	 */
	protected function formatTime(int|float $seconds)
	{
		if (!is_numeric($seconds) || $seconds < 0) {
			Log::warning("Invalid time value: " . $seconds);
			return;
		}

		// Extract whole minutes
		$minutes = floor($seconds / 60);

		// Remaining seconds (with decimals)
		$remainingSeconds = $seconds - ($minutes * 60);

		// Format with leading zeros and 2 decimal places
		return sprintf("%02d:%05.2f", $minutes, $remainingSeconds);
	}

	/**
	 * Decode JSON response from remote source
	 *
	 * @param  string $response
	 * @return array|false	Return decoded response in array, false on failure
	 */
	protected function decodeJson(string $response)
	{
		$res = json_decode($response, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			Log::error($response . ' is not a valid JSON response, reason: ' . json_last_error_msg());
			return false;
		}
		return $res;
	}
	protected function krc2lrc(string $krcText)
	{
		$lyricText = "";
		$metaRegex = "/^\[(\S+):(\S+)\]$/";
		$timestampsRegex = "/^\[(\d+),(\d+)\]/";
		$timestamps2Regex = "/<(\d+),(\d+),(\d+)>([^<]*)/";
		$lines = preg_split("/[\r\n]/", $krcText);
		foreach ($lines as $idx => $line) {
			if (preg_match($metaRegex, $line, $matches)) { // meta info
				if (in_array($matches[1], ['language', 'sign', 'id'])) continue;
				else if (in_array($matches[1], ['total'])) {
					$lyricText .= '[length: ' . gmdate('i:s', floor($matches[2])) . "]\r\n";
					continue;
				} else if (in_array($matches[1], ['ar', 'ti']) && is_numeric($matches[2]))
					continue;//skip artist and title meta info if it's numeric
				$lyricText .= $matches[0] . "\r\n";
			} else if (preg_match($timestampsRegex, $line, $matches)) {
				$lyricLine = "";
				$startTime = (int)$matches[1];
				$duration = (int)$matches[2];
				if ($idx === 0) {
					if ($startTime > 5000)
						$lyricLine = "[" . $this->formatTime($startTime / 1000 - 5) . "]";
					else $lyricLine = "[00:00.00]";
				} else $lyricLine = "[" . $this->formatTime($startTime / 1000) . "]";

				// parse sub-timestamps
				if (preg_match_all($timestamps2Regex, $line, $subMatches)) {
					for ($a = 0; $a < count($subMatches[0]); $a++) {
						$offset = (int)$subMatches[1][$a];
						$subWord = $subMatches[4][$a];
						$lyricLine .= "<" . $this->formatTime(($startTime + $offset) / 1000) . ">" . $subWord;
					}
				}
				$lyricText .= $lyricLine . "<" . $this->formatTime(($startTime + $duration) / 1000) . "> \r\n";
			}
		}
		return $lyricText;
	}
}
