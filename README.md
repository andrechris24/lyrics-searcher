# Lyrics Searcher

<p>A Laravel based lyrics searcher to Kugou, Musixmatch, NetEase, QQ Music, Soda Music, and LRCLib, plus optionally Local database and Lyrics.ovh. This lyrics searcher contains quick search and per-provider lyrics search. This lyrics searcher also provides unencrypted LRC converter from SRT, KRC, and QRC.</p>

> [!CAUTION]
> All requests to Musixmatch are throttled intentionally due to strict rate limit.

## Tech Stack

- Laravel 13 with PHP 8.4
- Bootstrap 5
- FontAwesome v7
- Backpack CRUD v7
- DataTables v2
- sweetalert2
- jQuery Loading Overlay

## Setup

Make sure `php_openssl` extension is enabled. If you want to self host, enable SSL extension on web server too. This command below are for Windows, and all commands eare same for Linux.

```
# Clone and install
git clone git@github.com:andrechris24/lyrics-searcher.git
cd lyrics-searcher
composer install
npm install

# Set Environment
copy .env.example .env #Linux: use cp instead of copy
php artisan key:generate
php artisan storage:link

# Setup Database
php artisan migrate --seed

# Generate Musixmatch token, then manually copy paste to MUSIXMATCH_TOKEN in .env file
php artisan usertoken

# Run development server
php artisan serve

# Optional
php artisan backpack:user
```

> [!IMPORTANT]
> Make sure the `APP_URL` was set properly in .env file, like `http://127.0.0.1:8000` or `http://sample.test`.

## Admin credentials

> [!NOTE]
> This admin credential is for local environment only. Hosted application uses different admin credentials and register is disabled for security reasons.

Email: <test@example.com>\
Password: password

<!-- > [!TIP]
> If you are logged in to admin panel, you can upload lyric files by go to Local search page and select **Upload Lyrics**. -->

## Troubleshooting

### Problem: Got `cURL error 60: SSL certificate problem: unable to get local issuer certificate` while searching lyrics

Solution: 
1. Download [cacert.pem](https://curl.se/ca/cacert.pem) and save to `\path\to\php\extras\ssl`.
	- For example `C:\wamp64\bin\php\php8.4.21\extras\ssl`
2. Open php.ini file, set `curl.cainfo` value to `"path\to\php\extras\ssl\cacert.pem"` (with quotes),  remove ; at leading and save.
3. Restart php and web server if applicable.

## References

- [Lyricify-Lyrics-Helper](https://github.com/WXRIW/Lyricify-Lyrics-Helper)
- [ESLyric](https://github.com/ESLyric/scripts)
- [foo_openlyrics](https://github.com/jacquesh/foo_openlyrics)
- [MxLRC](https://github.com/fashni/MxLRC)

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
