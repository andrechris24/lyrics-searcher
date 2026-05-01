<?php

namespace App;

class KrcDecoder
{
	/**
	 * Create a new class instance.
	 */
	public function __construct()
	{
		//
	}
	private static function krchex_xor($s)
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
	public static function decode($str){
		$zip = self::krchex_xor(base64_decode($str));
		if (!$zip) abort(500, 'Failed to decrypt KRC data');
		$decoded = zlib_decode($zip);
		if (!$decoded) abort(500, 'Failed to decode KRC data');
		return $decoded;
	}
}
