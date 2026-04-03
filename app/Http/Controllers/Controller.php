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
	protected function getMXerror($header)
	{
		if (array_key_exists('hint', $header)) {
			if ($header['hint'] === 'renew') $msg = "Invalid Musixmatch token";
			elseif ($header['hint'] === 'captcha') $msg = "Musixmatch blocked your IP";
			else $msg = "Musixmatch returned an error with reason: " . $header['hint'];
		} else if ($header['status_code'] === 401)
			$msg = "Musixmatch rate limit exceeded. Please try again later.";
		else if ($header['status_code'] === 404)
			$msg = "Musixmatch query returned no result";
		else $msg = "Musixmatch HTTP Error " . $header['status_code'];
		return $msg;
	}

	/**
	 * Convert seconds (with decimals) to mm:ss.xx format
	 *
	 * @param float $seconds
	 * @return string
	 */
	protected function formatTime($seconds)
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

	protected function krchex_xor($s)
	{
		$magic_bytes = "\x6b\x72\x63\x31"; // 'k' , 'r' , 'c' ,'1'
		if (strlen($s) < strlen($magic_bytes)) return;
		for ($i = 0; $i < strlen($magic_bytes); ++$i) {
			$c = $s[$i];
			if ($c != $magic_bytes[$i]) return;
		}

		$enc_key = "\x40\x47\x61\x77\x5e\x32\x74\x47\x51\x36\x31\x2d\xce\xd2\x6e\x69";
		$buf = "";
		$krc_header = strlen($magic_bytes); //first 4 bytes
		for ($i = $krc_header; $i < strlen($s); ++$i) {
			$x1 = $s[$i];
			$x2 = $enc_key[($i - $krc_header) % 16];
			$buf .= ($x1 ^ $x2);
		}
		return $buf;
	}
	protected function krc2lrc($krcText)
	{
		$lyricText = "";
		$metaRegex = "/^\[(\S+):(\S+)\]$/";
		$timestampsRegex = "/^\[(\d+),(\d+)\]/";
		$timestamps2Regex = "/<(\d+),(\d+),(\d+)>([^<]*)/";
		$lines = preg_split("/[\r\n]/", $krcText);
		foreach ($lines as $line) {
			if (preg_match($metaRegex, $line, $matches)) { // meta info
				if (in_array($matches[1], ['language', 'ar', 'id', 'ti'])) continue;
				$lyricText .= $matches[0] . "\r\n";
			} else if (preg_match($timestampsRegex, $line, $matches)) {
				$lyricLine = "";
				$startTime = (int)$matches[1];
				$duration = (int)$matches[2];
				$lyricLine = "[" . $this->formatTime($startTime / 1000) . "]";

				// parse sub-timestamps		
				if (preg_match_all($timestamps2Regex, $line, $subMatches)) {
					for ($a = 0; $a < count($subMatches[0]); $a++) {
						$offset = (int)$subMatches[1][$a];
						$subWord = $subMatches[4][$a];
						$lyricLine .= "<" . $this->formatTime(($startTime + $offset) / 1000) . ">" . $subWord;
					}
				}
				$lyricLine .= "<" . $this->formatTime(($startTime + $duration) / 1000) . "> ";
				$lyricText .= $lyricLine . "\r\n";
			}
		}
		return $lyricText;
	}
}
