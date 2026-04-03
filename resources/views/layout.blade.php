<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>@yield('title') | Lyrics Searcher</title>

		<!-- Bootstrap CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="{{ asset('css/theme.css') }}">

		<!-- FontAwesome -->
		<link rel="stylesheet"
			href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
			integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
			crossorigin="anonymous" referrerpolicy="no-referrer" />

		<link href="https://cdn.datatables.net/v/bs5/dt-2.3.7/r-3.0.8/datatables.min.css"
			rel="stylesheet"
			integrity="sha384-6SydH6I4YnZdQJwxGJm7CTO/99Gi64VIvO2OVodF01nVIomEkil0N5WscsmC9+Dz"
			crossorigin="anonymous">
		<script type="text/javascript" src="{{ asset('js/theme.js') }}"></script>
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
		<div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
			<button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
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
		</div>
		<nav class="navbar navbar-expand-lg bg-body-tertiary">
			<div class="container-fluid">
				<a class="navbar-brand" href="{{ route('home') }}">Navbar</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse"
					data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
					aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<li class="nav-item">
							<a @class(['nav-link', 'active' => request()->routeIs('home')])
								@if (request()->routeIs('home')) aria-current="page" @endif
								href="{{ route('home') }}">
								Home
							</a>
						</li>
						<li class="nav-item">
							<a @class(['nav-link', 'active' => request()->routeIs('lrclib.*')])
								@if (request()->routeIs('lrclib.*')) aria-current="page" @endif
								href="{{ route('lrclib.index') }}">LRCLib</a>
						</li>
						<li class="nav-item">
							<a @class(['nav-link', 'active' => request()->routeIs('qqmusic.*')])
								@if (request()->routeIs('qqmusic.*')) aria-current="page" @endif
								href="{{ route('qqmusic.index') }}">QQ Music</a>
						</li>
						<li class="nav-item">
							<a @class(['nav-link', 'active' => request()->routeIs('netease.*')])
								@if (request()->routeIs('netease.*')) aria-current="page" @endif
								href="{{ route('netease.index') }}">NetEase</a>
						</li>
						<li class="nav-item">
							<a @class(['nav-link', 'active' => request()->routeIs('kugou.*')])
								@if (request()->routeIs('kugou.*')) aria-current="page" @endif
								href="{{ route('kugou.index') }}">Kugou</a>
						</li>
						@if (!empty(env('MUSIXMATCH_TOKEN')))
							<li class="nav-item">
								<a @class(['nav-link', 'active' => request()->routeIs('musixmatch.*')])
									@if (request()->routeIs('musixmatch.*')) aria-current="page" @endif
									href="{{ route('musixmatch.index') }}">Musixmatch</a>
							</li>
						@endif
					</ul>
				</div>
			</div>
		</nav>
		<div class="container">
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
		<script src="https://cdn.datatables.net/v/bs5/dt-2.3.7/r-3.0.8/datatables.min.js"
			integrity="sha384-5L6UP+VtXWFTfdyUlr1LWG1lDU276xtuJbHZbCldV4v0FxVOCmuIN4SNnMsTMrGF"
			crossorigin="anonymous"></script>
		<script type="text/javascript">
			$.ajaxSetup({
				timeout: 30000
			});
			const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
			const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap
				.Tooltip(tooltipTriggerEl));
			const toast = Swal.mixin({
				toast: true,
				position: "top-end",
				showConfirmButton: false,
				timer: 5000,
				timerProgressBar: true,
				theme: 'bootstrap-5',
				didOpen: (toast) => {
					toast.onmouseenter = Swal.stopTimer;
					toast.onmouseleave = Swal.resumeTimer;
				}
			});

			function blobDL(data, filename) {
				const blob = new Blob([data], {
					type: "text/plain",
					charset: "utf-8"
				});
				let url = window.URL.createObjectURL(blob),
					a = document.createElement("a");
				a.href = url;
				a.download = filename;
				document.body.appendChild(a);
				a.click();
				a.remove();
				window.URL.revokeObjectURL(url);
			}
		</script>
		@yield('js')
	</body>

</html>
