<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title>@yield('title') | {{ env('APP_NAME') }}</title>

		<!-- Bootstrap CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous">

		<!-- FontAwesome -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.2.0/css/all.min.css" integrity="sha256-MVopmdyC2tYTiJ8wlktf0uh0v4NgT+vNdyVFepi7Q0c=" crossorigin="anonymous">

		<link href="https://cdn.datatables.net/v/bs5/dt-2.3.8/r-3.0.8/datatables.min.css" rel="stylesheet" integrity="sha384-tNYQLr593NFEx21InOh9Hbxz0c66iRCODxYVibI1MHkEHCTPXTwB/7k4ZqWvtRt9" crossorigin="anonymous">
		<script type="text/javascript" src="{{ asset('js/theme.js') }}"></script>
		<style>
			.bi {
				vertical-align: -0.125em;
				fill: currentColor;
			}
		</style>
	</head>

	<body>
		<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
			<symbol id="circle-half" viewBox="0 0 16 16">
				<path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
			</symbol>
			<symbol id="moon-stars-fill" viewBox="0 0 16 16">
				<path
					d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
				<path
					d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
			</symbol>
			<symbol id="sun-fill" viewBox="0 0 16 16">
				<path
					d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
			</symbol>
		</svg>
		<nav class="navbar navbar-expand-lg bg-body-tertiary">
			<div class="container-fluid">
				<a class="navbar-brand" href="{{ route('home') }}">
					<i class="fa-solid fa-music fa-2x"></i>
				</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse"
					data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
					aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav flex-row flex-wrap">
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('home')])
								@if (request()->routeIs('home')) aria-current="page" @endif
								href="{{ route('home') }}">
								Home
							</a>
						</li>
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('lrclib.*')])
								@if (request()->routeIs('lrclib.*')) aria-current="page" @endif
								href="{{ route('lrclib.index') }}">LRCLib</a>
						</li>
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('qqmusic.*')])
								@if (request()->routeIs('qqmusic.*')) aria-current="page" @endif
								href="{{ route('qqmusic.index') }}">QQ</a>
						</li>
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('netease.*')])
								@if (request()->routeIs('netease.*')) aria-current="page" @endif
								href="{{ route('netease.index') }}">NetEase</a>
						</li>
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('kugou.*')])
								@if (request()->routeIs('kugou.*')) aria-current="page" @endif
								href="{{ route('kugou.index') }}">Kugou</a>
						</li>
						<li class="nav-item dropdown col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class([
								'nav-link',
								'dropdown-toggle',
								'active' => request()->routeIs('musixmatch.*'),
								'disabled' => empty(env('MUSIXMATCH_TOKEN'))
							]) href="#"
								@if (request()->routeIs('musixmatch.*')) aria-current="page" @endif role="button"
								data-bs-toggle="dropdown" aria-expanded="false"
								@if (empty(env('MUSIXMATCH_TOKEN'))) aria-disabled="true" @endif>
								Musixmatch
							</a>
							<ul class="dropdown-menu">
								<li>
									<a @class([
										'dropdown-item',
										'active' =>
											request()->routeIs('musixmatch.index') ||
											request()->routeIs('musixmatch.search*') ||
											request()->routeIs('musixmatch.advanced')
									])
										@if (request()->routeIs('musixmatch.index') ||
												request()->routeIs('musixmatch.search*') ||
												request()->routeIs('musixmatch.advanced')
										) aria-current="page" @endif href="{{ route('musixmatch.index') }}">
										Search
									</a>
								</li>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li>
									<a @class([
										'dropdown-item',
										'active' => request()->is('musixmatch/charts/top')
									])
										@if (request()->is('musixmatch/charts/top')) aria-current="page" @endif
										href="{{ route('musixmatch.chart', ['type' => 'top']) }}">
										Top chart
									</a>
								</li>
								<li>
									<a @class([
										'dropdown-item',
										'active' => request()->is('musixmatch/charts/hot')
									])
										@if (request()->is('musixmatch/charts/hot')) aria-current="page" @endif
										href="{{ route('musixmatch.chart', ['type' => 'hot']) }}">
										Popular lyrics
									</a>
								</li>
							</ul>
						</li>
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('sodamusic.*')])
								@if (request()->routeIs('sodamusic.*')) aria-current="page" @endif
								href="{{ route('sodamusic.index') }}">Soda Music</a>
						</li>
						<li class="nav-item col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class(['nav-link', 'active' => request()->routeIs('local.*')])
								@if (request()->routeIs('local.*')) aria-current="page" @endif
								href="{{ route('local.index') }}">
								Local
							</a>
						</li>
						<li class="nav-item dropdown col-sm-6 col-md-4 col-lg-auto col-12">
							<a @class([
								'nav-link',
								'dropdown-toggle',
								'active' =>
									request()->routeIs('deezer.*') ||
									request()->routeIs('spotify.*') ||
									request()->routeIs('youtube.*')
							])
								@if (request()->routeIs('deezer.*') ||
										request()->routeIs('spotify.*') ||
										request()->routeIs('youtube.*')
								) aria-current="page" @endif href="#"
								role="button" data-bs-toggle="dropdown" aria-expanded="false">
								More
							</a>
							<ul class="dropdown-menu">
								<li>
									<a @class(['dropdown-item', 'active' => request()->routeIs('deezer.*')]) href="{{ route('deezer.index') }}"
										@if (request()->routeIs('deezer.*')) aria-current="page" @endif >
										Deezer
									</a>
								</li>
								<li>
									<a @class(['dropdown-item', 'active' => request()->routeIs('spotify.*')]) href="{{ route('spotify.index') }}"
										@if (request()->routeIs('spotify.*')) aria-current="page" @endif >
										Spotify
									</a>
								</li>
								<li>
									<a @class(['dropdown-item', 'active' => request()->routeIs('youtube.*')]) href="{{ route('youtube.index') }}"
										@if (request()->routeIs('youtube.*')) aria-current="page" @endif >
										YouTube
									</a>
								</li>
								<li>
									<a @class(['dropdown-item', 'active' => request()->routeIs('apple.*')]) href="{{ route('apple.index') }}"
										@if (request()->routeIs('apple.*')) aria-current="page" @endif >
										Apple Music
									</a>
								</li>
							</ul>
						</li>
					</ul>
					<hr class="d-lg-none text-white-50">
					<ul class="navbar-nav flex-row flex-wrap ms-md-auto">
						<li class="nav-item">
							<button class="btn btn-link nav-link px-0 px-lg-1" data-bs-toggle="modal"
							data-bs-target="#modalConvert">Convert</button>
						</li>
						@auth(backpack_guard_name())
						<li class="nav-item active">
							<a href="{{ route('backpack') }}" class="nav-link">Admin</a>
						</li>
						@endauth
						<li class="nav-item py-2 py-lg-1 col-12 col-lg-auto">
							<div class="vr d-none d-lg-flex h-100 mx-lg-1 text-white"></div>
							<hr class="d-lg-none my-2 text-white-50">
						</li>
						<li class="nav-item dropdown">
							<button class="btn me-2 dropdown-toggle d-flex align-items-center"
								id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown"
								aria-label="Toggle theme (auto)">
								<svg class="bi my-1 theme-icon-active" width="1em" height="1em">
									<use href="#circle-half"></use>
								</svg>
								<span class="visually-hidden" id="bd-theme-text">Theme Toggle</span>
							</button>
							<ul class="dropdown-menu mt-lg-2" aria-labelledby="bd-theme-text">
								<li>
									<a href="#" class="dropdown-item" data-bs-theme-value="light"
										aria-pressed="false">
										<svg class="bi me-2" width="1em" height="1em">
											<use href="#sun-fill"></use>
										</svg>
										Light
									</a>
								</li>
								<li>
									<a href="#" class="dropdown-item" data-bs-theme-value="dark"
										aria-pressed="false">
										<svg class="bi me-2" width="1em" height="1em">
											<use href="#moon-stars-fill"></use>
										</svg>
										Dark
									</a>
								</li>
								<li>
									<a href="#" class="dropdown-item" data-bs-theme-value="auto"
										aria-pressed="true">
										<svg class="bi me-2" width="1em" height="1em">
											<use href="#circle-half"></use>
										</svg>
										Auto
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>
		<div class="modal fade" tabindex="-1" id="modalConvert"
			aria-labelledby="modalConvertLabel" role="dialog" aria-hidden="true">
			<div role="document"
				class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 id="modalConvertLabel" class="modal-title">Convert lyric</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"
							aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form class="mb-5" id="lyric-converter-form">
							<div class="mb-3">
								<label for="convert-type" class="form-label">
									Conversion type
								</label>
								<select class="form-select" name="type" id="convert-type" required>
									<option value="" selected>Choose</option>
									<option value="fromSrt">SRT to LRC</option>
									<option value="fromKrc">KRC to LRC</option>
								</select>
							</div>
							<div class="mb-3">
								<label for="source-file-to-convert" class="form-label">
									Select file
								</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fa-solid fa-file-lines"></i>
									</span>
									<input type="file" class="form-control" id="source-file-to-convert"
										accept=".srt, .krc" name="source-file" required>
								</div>
							</div>
						</form>
						<div class="mb-3">
							<b>Conversion result:</b>
							<p id="converted-lyric" style="white-space: pre-line">...</p>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
							Close
						</button>
						<button type="button" class="btn btn-success" id="save-converted" disabled>
							Save
						</button>
						<button type="submit" class="btn btn-primary" form="lyric-converter-form">
							Convert
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-3">
				<h3 class="mt-5 pt-5 text-center">@yield('subpage-title')</h3>
				@if (Session::has('error') || $errors->any())
					<x-error /> @endif
			</div>
			@yield('content')
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
			integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
			crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/jquery-3.7.1.min.js"
			integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
			crossorigin="anonymous"></script>
		<script
			src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"
			integrity="sha256-jLFv9iIrIbqKULHpqp/jmePDqi989pKXOcOht3zgRcw="
			crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script src="https://cdn.datatables.net/v/bs5/dt-2.3.8/r-3.0.8/datatables.min.js"
			integrity="sha384-4GcaTyTewMDCAbLVgOubCZiRYKyuZ+uHk2sLNSwuCz0Bi8kf2R6cg6P0iNVIH5XE"
			crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/pako@2.1.0/dist/pako.min.js"
			integrity="sha256-7eJpOkpqUSa501ZpBis1jsq2rnubhqHPMC/rRahRSQc="
			crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/luxon@3.7.2/build/global/luxon.min.js"
			integrity="sha256-UWxf6lyCvfH7XEcrmq/cqc2m3IBrLBLOnWLC4Pw0gQQ="
			crossorigin="anonymous"></script>
		<script type="text/javascript" src="{{ asset('js/convert.js') }}"></script>
		<script type="text/javascript" src="{{ asset('js/setup.js') }}"></script>
		@yield('js')
	</body>

</html>
