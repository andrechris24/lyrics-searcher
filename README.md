# LRCSearch

<p align="center">A Laravel based lyrics searcher to Kugou, Musixmatch, NetEase, QQ Music, LRCLib, Deezer, Spotify, and Apple Music, plus optionally Local database and Lyrics.ovh. This lyrics searcher contains quick search and per-provider lyrics search. LRCSearch also provides LRC converter from SRT and KRC.</p>

> [!CAUTION]
> Due to API change, API Token is required for Deezer and Spotify. To get API Token, read [steps to get API Token](#steps-to-get-api-token) in Setup section. 

**This repository is the source code of [hosted application](https://andrechris24.serv00.net).**

## Tech Stack

- Laravel 13 with PHP 8.4
- CoreUI 5
- FontAwesome v7
- Backpack CRUD v7
- DataTables v2
- sweetalert2
- jQuery Loading Overlay

## Setup

> [!IMPORTANT]
> Make sure `php_openssl` extension is enabled and the `APP_URL` was set properly in .env file, like `http://127.0.0.1:8000` or `http://sample.test`. If you want to self host, enable SSL extension on web server too. This command below are for Windows, and all commands except one are same for Linux. The env file contains `MINILYRICS_COMPATIBLE` setting if you use MiniLyrics (which contains bugs for Enhanced LRC), defaults to true.

```sh
# Clone and install
git clone git@github.com:andrechris24/lyrics-searcher.git
cd lyrics-searcher
composer install
pnpm install #Optional, this is for eslint. You can use npm instead of pnpm

# Set Environment
copy .env.example .env #Linux: use cp instead of copy
php artisan key:generate
php artisan storage:link

# Setup Database
php artisan migrate --seed

# Generate Musixmatch fallback token
php artisan usertoken

# Run development server
php artisan serve

# Optional
php artisan backpack:user
```

#### Steps to get API Token

Required for Deezer and Spotify
1. Log in to https://api.paxsenix.org/dashboard with your GitHub account.
2. After log in, navigate to **API Keys**. Generate key, then copy generated key.
3. Open .env file, then paste copied key into **PAXSENIX_TOKEN** entry and Save.

## Admin credentials

> [!NOTE]
> This admin credential is for local environment only. Hosted application uses different admin credentials, uses different admin link, and register is disabled for security reasons. To access admin panel, goto `http://127.0.0.1:8000/admin`.

Email: <test@example.com>\
Password: password

<!-- > [!TIP]
> If you are logged in to admin panel, you can upload lyric files by go to Local search page and select **Upload Lyrics**. -->

## Troubleshooting

### Got `cURL error 60: SSL certificate problem: unable to get local issuer certificate` while searching lyrics

Solution: 
1. Download [cacert.pem](https://curl.se/ca/cacert.pem) and save to `\path\to\php\extras\ssl`.
	- Example: `C:\wamp64\bin\php\php8.4.21\extras\ssl`
2. Open php.ini file, set `curl.cainfo` value to `"path\to\php\extras\ssl\cacert.pem"` (with quotes), remove ; at leading and save.
	- Example: `"C:\wamp64\bin\php\php8.4.21\extras\ssl\cacert.pem"`
3. Restart php and web server if applicable.

## Why aimp_webLyrics.ini file exist?

That was my version of `aimp_webLyrics.ini` file containing remote source from providers above (except Kugou) and some codes are taken from AIMP WebLyrics forum. If you want, do setup first, then run `php artisan usertoken` to generate token and replace all `<MX_TOKEN_X>` inside INI file, and the token should be different. After entering Musixmatch token, replace original INI file inside `\path\to\AIMP\Plugins\aimp_webLyrics` folder.

- Example path (Windows): `C:\Program Files\AIMP\Plugins\aimp_webLyrics`

> [!WARNING]
> Every AIMP updates resets aimp_webLyrics.ini file, so you need to replace again. Although AIMP can read Enhanced LRC ([with additional plugin](https://aimp.ru/?do=catalog&rec_id=1391)), WebLyrics plugin will remove Enhanced LRC timestamps when lyrics are fetched from remote source.

## References

- [Lyricify-Lyrics-Helper](https://github.com/WXRIW/Lyricify-Lyrics-Helper)
- [ESLyric](https://github.com/ESLyric/scripts)
- [foo_openlyrics](https://github.com/jacquesh/foo_openlyrics)
- [MxLRC](https://github.com/fashni/MxLRC)
