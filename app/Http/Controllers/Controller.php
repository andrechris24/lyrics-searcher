<?php

namespace App\Http\Controllers;

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
				401 => "Musixmatch rate limit exceeded. Please try again in a few minutes.",
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
		if (empty($krcText)) return null;
		$lyricText = "";
		$metaRegex = "/^\[(\S+):(\S+)\]$/";
		$timestampsRegex = "/^\[(\d+),(\d+)\]/";
		$timestamps2Regex = "/<(\d+),(\d+),(\d+)>([^<]*)/";
		$lines = preg_split("/[\n]/", $krcText);
		foreach ($lines as $idx => $line) {
			if (preg_match($metaRegex, $line, $matches)) { // meta info
				if (
					in_array($matches[1], ['language', 'sign', 'id']) ||
					(in_array($matches[1], ['ar', 'ti']) && is_numeric($matches[2]))
				) continue;
				else if (in_array($matches[1], ['total'])) {
					$lyricText .= '[length: ' . gmdate('i:s', floor($matches[2] / 1000)) . "]\n";
					continue;
				}
				$lyricText .= $matches[0] . "\n";
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
				$lyricText .= $lyricLine . "<" . $this->formatTime(($startTime + $duration) / 1000) . "> \n";
			}
		}
		return $lyricText;
	}
}
