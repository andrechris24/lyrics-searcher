<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\{ConnectionException, RequestException};
use Illuminate\Support\Facades\{Log, Session};
use Illuminate\Support\Str;
use JsonException;

abstract class Controller
{
	protected const APP_HEADER =
	['User-Agent' => 'LRCSearch/1.1 (https://github.com/andrechris24/lyrics-searcher)'];
	protected static string $paxsenix_url = "https://api.paxsenix.org/";
	protected static string $lyrically_url = "https://lyrics.paxsenix.org/";
	public const blacklistedTokens = [
		'UpgradeOnlyUpgradeOnlyUpgradeOnlyUpgradeOnly',
		'00000000000000000000000000000000000000000000000000000000'
	];
	protected const brokenMXResult = [
		'artist' => 'Drake',
		'title' => 'NOKIA',
		'album' => '$ome $exy $ongs 4 U',
		'link' => 'https://www.musixmatch.com/lyrics/Drake/NOKIA',
		'track_id' => 226291677,
		'subtitle_id' => 32661201,
		'lyric_id' => 35776330,
		'lyric' => [
			'Wob gopini den',
			'Tefe woxica fero',
			'Gogoh vudob wiya',
			'Keric sohu peduf',
			'Kahe rorew vip',
			'Vuqew qazaqo kimi',
			'Qaxu xur cutox',
			'Ger tiwuk gejun',
			'Yanufo zisan sen',
			'Poj hesu qanu',
			'Kij viki manex',
			'Yited foc weti',
			'Dumufu bav hebijo',
			'Xipihe wihe yigog',
			'Sogu qud seyoz',
			'Boj weq yec',
			'Kit tusoh yikel',
			'Berob yogipa wenebi',
			'Sut dupa jorum',
			'Vagij fevo cun',
			'Rab zomuj raya',
			'Hasoy muxife jer',
			'Vavi fib qudef',
			'Qequhu wuyo camat',
			'Rez pamer tetid',
			'Fegozu naxih siz',
			'Jayul vuvoze suw',
			'Pomebu zik vucas',
			'Xavu wudig lin',
			'Kabe yaja faye',
			'Yeso rupovi huseq',
			'Wege qabuma din',
			'Kuhi hewehu fim',
			'Jonuga romu kuj',
			'Hidol tizu jagil',
			'Vec nomut qel',
			'Xexaf juri jicu',
			'Kujuq woxef hejun',
			'Guk veke kudih',
			'Fiwaj fuqoki qel',
			'Keqiv tiyezu fawu',
			'Ranek ceha bofiju',
			'Fucad wiqun hoxewe',
			'Meteno lud winoyo',
			'Fovif low poz',
			'Feqi zavare baq',
			'Lisu bogajo godaju',
			'Fugam bojay yeviy',
			'Golizo mefa bubix',
			'Nocecu beyuka zid',
			'Jus pil peqize',
			'Vuhuv niv goci',
			'Yupem loqut yafol',
			'Warelu cesol rohire',
			'Qoju wox sinus',
			'Tak lurexo jugawe',
			'Nucico vudeb weru',
			'Rij bofigu gex',
			'Luko zimoya kalo',
			'Mov nifeze suy',
			'Deta berih rebe',
			'Qibapu lax son',
			'Qogopu cuga yato',
			'Roqo zupe woraq',
			'Daraj qacig nun',
			'Yiluxe jikox seru',
			'Vatewa yaj jaq',
			'Bodite xel jej',
			'Latala wodero senaro',
			'Kete bivim gegimi',
			'Giyoba yavegu vusul',
			'Qiq xafuto gadato',
			'Nec fozuse foquho',
			'Fiqur juruc peku',
			'Hereza guvo ludoma',
			'Dalul locufi wuh',
			'Yuv kez loham',
			'Pifel zum mace',
			'Sadigi voruvo lacol',
			'Wox nubiva mobab',
			'Hir dazaba rajiba',
			'Deneqo cof zacu',
			'Hixur cesi detuz',
			'Cihobe vigu rujol',
			'Hifafo wenemi zot',
			'Qub val gadoj',
			'Vemuve qaren zetuh',
			'Moni caf dac',
			'Yatojo miwo xed',
			'Cuj dekoc kihe',
			'Bimec caso fufid',
			'Zoqo zomita rod',
			'Ran xuxi dizek',
			'Gideti ruza los',
			'Hepuru muro rubuw',
			'Riro qopuki qifa',
			'Moj wujel vazazu',
			'Jehi gihijo subov',
			'Teboto xira cufaxa',
			'Num yix biduy',
			'Gef haqes kel',
			'Bureha yuya tib',
			'Feloh piveh vawo',
			'Miduf domadi fox',
			'Coh coyi xah',
			'Siyace rim rikuwa',
			'Cavay biba mej',
			'Dolun rima pezif',
			'Koh raqe caj',
			'Jezoy kahitu bavid',
			'Feto tohona niwij',
			'Buwo wumim keli',
			'Pufu sudagi zivo',
			'Voroso zomihe patur',
			'Sugi dixaha wamu',
			'Tosu yogodo wani',
			'Parafa fac voyi',
			'Jasob viwi ran',
			'Jocupe ledoga fuya',
			'Tusun jup soguto',
			'Guw kej semig',
			'Bugux kepusa cepefe',
			'Gakar gofa raco',
			'Luv meweji dida',
			'Viv zibum vita',
			'Vafomo wunupo nomad',
			'Tikava huv pey',
			'Zute colite duriho',
			'Yoqu woci ralov',
			'Lopin hus mewu',
			'Mefe gafuj jux',
			'Quqoy jol qamiq',
			'Gac rutibo wot',
			'Duvewo rup pawe',
			'Wubak gedozu vifuna',
			'Bid xiqa yad',
			'Loqep gapawe nehuj',
			'Vituso nubita heq',
			'Xad doyo doko',
			'Bobeg fepepa quvo',
			'Hikuw gecuqa qazuho',
			'Huca xuq moda',
			'Pisipa sej vohen',
			'Hek wiya waci',
			'Jowasi memoha cecuj',
			'Qeyoc duk gexij',
			'Mibi bawok mesuj',
			'Lok sov ceca',
			'Yuguza get suk',
			'Loco qikek muwi',
			'Lici tozic muda',
			'Nolocu qovu kapuk',
			'Ruxu niga qusuro',
			'Satuf saloku bexo',
			'Xiw jal nikiqa',
			'Poya kovus xijeq',
			'Qike jan jaruya',
			'Zoh ripo xej',
			'Xetiya zufuj gur',
			'Qupife cos picu',
			'Yiz yuqi bisi',
			'Yocej bayoz laguku',
			'Pat huyi qavoji',
			'Bafako locun zef',
			'Fukul kamok dekam',
			'Miv saneda hepol',
			'Kog sope jaqid',
			'Jen deti rekem',
			'Tecequ wuxoh cuvo',
			'Jada rovud yorata',
			'Xaradu johiyo ceyusa',
			'Gem hama fexa',
			'Sim kih tuto',
			'Ges hulobe fek',
			'Hosid puf kal',
			'Waqu robo haxa',
			'Der rafele dujo',
			'Ric toxuy qof',
			'Sob bunob jes',
			'Jadeje tawu rago',
			'Memi pevi wugo',
			'Lep riveg del',
			'Pewip fenux casari',
			'Xus tiwupo pedi',
			'Net qorixo zasu',
			'Karof wum peqeq',
			'Govid kusila bek',
			'Dav ruz gorazi',
			'Ridi cequwa moti',
			'Kitaka ham rev',
			'Suxowe leluc peruju',
			'Horihe kalub set',
			'Tuzip qeyeva poyah',
			'Tec rijiw kif',
			'Sagayo kanace nexeje',
			'Diqadi wipujo bic',
			'Cuda seto saqo',
			'Giva picuqa teqe',
			'Behebo zivula leniy',
			'Yogaj zebexo vay',
			'Tevuda velux nim',
			'Geye nehak kaj',
			'Cap dopobi fuh',
			'Bitu jagam sub',
			'Zox pus yanu',
			'Tiham tosuz zezebu'
		]
	];

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
				'endpoint not found' => 'This Musixmatch URL was invalid or retired, please contact site owner.',
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

	/**
	 * Get Lyrically & Paxsenix error message by exception type
	 * @param  mixed        $e		Error Exception class
	 * @param  bool         $paxsenix Whether to include Paxsenix-specific error handling
	 * @return string							Error message by Exception class
	 */
	protected static function lyricallyError(mixed $e, bool $paxsenix = true): string
	{
		if ($paxsenix === true) $provider = 'Paxsenix';
		else $provider = 'Lyrically';
		if (get_class($e) === RequestException::class) {
			Log::warning('Request failed for ' . $e->response->effectiveUri());
			$json = $e->response->json();
			if (!$json) $reqerr = "$provider API Error {$e->response->status()}";
			// else if ($lrc === true && $e->response->status() === 404)
			// 	$reqerr = 'No lyric found for this song';
			else
				$reqerr = $json['message'] ?? $json['error'] ?? $json['detail'] ?? "$provider API Error {$e->response->status()}";
		}
		Log::error($e);
		return match (get_class($e)) {
			JsonException::class => "Malformed $provider API response ({$e->getMessage()})",
			ConnectionException::class => "$provider API connection error, {$e->getMessage()}",
			RequestException::class => $reqerr,
			default => "$provider API unexpected error"
		};
	}

	/**
	 * Sets LRC by: tag to source of LRC file if empty or unavailable
	 * @param string $lrc    Content of LRC file
	 * @param string $source Source of LRC file
	 */
	protected static function setLrcAuthor(string $lrc, string $source): string
	{
		if (!Str::contains($lrc, '[by:')) $lrc = "[by:$source]\n$lrc";
		return Str::replace('[by:]', "[by:$source]", $lrc);
	}
}
