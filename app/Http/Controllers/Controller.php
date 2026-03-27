<?php

namespace App\Http\Controllers;

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
		if (!is_numeric($seconds) || $seconds < 0) return "Invalid input";

		// Extract whole minutes
		$minutes = floor($seconds / 60);

		// Remaining seconds (with decimals)
		$remainingSeconds = $seconds - ($minutes * 60);

		// Format with leading zeros and 2 decimal places
		return sprintf("%02d:%05.2f", $minutes, $remainingSeconds);
	}
}
