@extends('layout')
@section('title', 'Spotify Charts')
@section('subpage-title', 'Spotify Charts')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<a href="{{ route('spotify.index') }}">Back to search</a>
	</div>
	@include('spotify.modal')
	<ul class="nav nav-tabs" id="spotifyChartTab" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="highlight-tab" data-coreui-toggle="tab"
				data-coreui-target="#highlight-tab-pane" type="button" role="tab"
				aria-controls="highlight-tab-pane" aria-selected="true">Highlight</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="track-tab" data-coreui-toggle="tab"
				data-coreui-target="#track-tab-pane" type="button" role="tab"
				aria-controls="track-tab-pane" aria-selected="false">Tracks</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="album-tab" data-coreui-toggle="tab"
				data-coreui-target="#album-tab-pane" type="button" role="tab"
				aria-controls="album-tab-pane" aria-selected="false">Albums</button>
		</li>
	</ul>
	<div class="tab-content" id="spotifyChartTabContent">
		<div class="tab-pane fade show active" id="highlight-tab-pane" role="tabpanel"
			aria-labelledby="highlight-tab" tabindex="0">
			<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
				@foreach ($highlights as $hl)
					<div class="col">
						<div class="card">
							<img src="{{ $hl['image'] }}" class="card-img-top" alt="{{ $hl['type'] }}">
							<div class="card-header">
								{{ $hl['type'] }}
							</div>
							<div class="card-body">
								<h5 class="card-title">
									{{ $hl['text'] }}
								</h5>
								<p class="card-text">{{ $hl['id'] }}</p>
							</div>
						</div>
					</div>
				@endforeach
			</div>
		</div>
		<div class="tab-pane fade" id="track-tab-pane" role="tabpanel"
			aria-labelledby="track-tab" tabindex="0">
			{{-- <div class="alert alert-danger d-flex align-items-center">
				<i class="fas fa-exclamation-triangle"></i>
				<div>
					Spotify lyrics are currently unavailable due to API issue.
					It will be available again after issue fixed.
				</div>
			</div> --}}
			<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
				@foreach ($track_chart as $tr)
					@php
						$artists = [];
						foreach ($tr['artists'] as $artist) {
							$artists[] = $artist;
						}
						$artistName = implode(', ', $artists);
						switch ($tr['movement']) {
							case 'MOVED_UP':
								$color = 'text-bg-success';
								$teks = '<i class="fa-solid fa-caret-up"></i>';
								break;
							case 'MOVED_DOWN':
								$color = 'text-bg-danger';
								$teks = '<i class="fa-solid fa-caret-down"></i>';
								break;
							case 'NO_CHANGE':
								$color = 'text-bg-secondary';
								$teks = '=';
								break;
							case 'NEW_ENTRY':
								$color = 'text-bg-primary';
								$teks = 'NEW';
								break;
							default:
								$color = 'text-bg-info';
								$teks = $tr['movement'];
								break;
						}
					@endphp
					<div class="col">
						<div class="card">
							<img src="{{ $tr['image'] }}" class="card-img-top" alt="{{ $tr['name'] }}">
							<div class="card-header">
								{{ $tr['rank'] }} <span
									class="badge {{ $color }}">{!! $teks !!}</span>
							</div>
							<div class="card-body">
								<h5 class="card-title">
									{{ $tr['name'] }}
								</h5>
								<p class="card-text">{{ $artistName }}</p>
							</div>
							<div class="card-footer">
								<div class="btn-group" role="group">
									<button class="btn btn-primary" data-coreui-id="{{ $tr['id'] }}"
										data-coreui-artist="{{ $artistName }}"
										data-coreui-track="{{ $tr['name'] }}"data-coreui-toggle="modal"
										data-coreui-target="#modalMX">
										<i class="fa-solid fa-eye" data-coreui-toggle="tooltip"
											data-coreui-title="Show & download lyric"></i>
									</button>
									<button type="button" class="btn btn-secondary download-btn"
										@disabled(empty(env('PAXSENIX_TOKEN')))
										data-href="https://open.spotify.com/track/{{ $tr['id'] }}"
										data-artist="{{ $artistName }}" data-title="{{ $tr['name'] }}"
										data-coreui-toggle="tooltip" data-coreui-title="Download song">
										<i class="fa-solid fa-download"></i>
									</button>
									<a href="https://open.spotify.com/track/{{ $tr['id'] }}"
										@class(['btn', 'btn-success', 'disabled' => empty($tr['id'])]) aria-disabled="{{ empty($tr['id']) }}"
										data-coreui-toggle="tooltip" data-coreui-title="Go to Spotify" target="_blank">
										<i class="fa-brands fa-spotify"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				@endforeach
			</div>
		</div>
		<div class="tab-pane fade" id="album-tab-pane" role="tabpanel"
			aria-labelledby="album-tab" tabindex="0">
			<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
				@foreach ($album_chart as $al)
					@php
						$artists = [];
						foreach ($al['artists'] as $artist) {
							$artists[] = $artist;
						}
						$artistName = implode(', ', $artists);
						switch ($al['movement']) {
							case 'MOVED_UP':
								$color = 'text-bg-success';
								$teks = '<i class="fa-solid fa-caret-up"></i>';
								break;
							case 'MOVED_DOWN':
								$color = 'text-bg-danger';
								$teks = '<i class="fa-solid fa-caret-down"></i>';
								break;
							case 'NO_CHANGE':
								$color = 'text-bg-secondary';
								$teks = '=';
								break;
							case 'NEW_ENTRY':
								$color = 'text-bg-primary';
								$teks = 'NEW';
								break;
							default:
								$color = 'text-bg-info';
								$teks = $al['movement'];
								break;
						}
					@endphp
					<div class="col">
						<div class="card">
							<img src="{{ $al['image'] }}" class="card-img-top" alt="{{ $al['name'] }}">
							<div class="card-header">
								{{ $al['rank'] }} <span
									class="badge {{ $color }}">{!! $teks !!}</span>
							</div>
							<div class="card-body">
								<h5 class="card-title">
									{{ $al['name'] }}
								</h5>
								<p class="card-text">{{ $artistName }}</p>
							</div>
							<div class="card-footer">
								<a href="https://open.spotify.com/album/{{ $al['id'] }}"
									@class(['btn', 'btn-success', 'disabled' => empty($al['id'])]) aria-disabled="{{ empty($al['id']) }}"
									data-coreui-toggle="tooltip" data-coreui-title="Go to Spotify" target="_blank">
									<i class="fa-brands fa-spotify"></i>
								</a>
							</div>
						</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/spotifychart.js') }}"></script>
@endsection
