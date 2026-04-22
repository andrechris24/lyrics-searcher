<x-no-script />
@empty($data)
	<x-no-results source="musixmatch" />
@else
	@if (!request()->routeIs('musixmatch.chart'))
		<p class="text-center">Page {{ request('page') ?? 1 }} out of
			{{ $header['available'] }} result(s), showing 20 results per page</p>
	@endif
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			@php
				$track = $result['track'];
				$length = gmdate('i:s', $track['track_length']);
				if ($track['instrumental']) {
					$lyricType = 'Instrumental';
					$color = 'text-warning';
				} elseif ($track['has_subtitles']) {
					$lyricType = 'Synced';
					$color = 'text-primary';
				} elseif ($track['has_lyrics']) {
					$lyricType = 'Plain';
					$color = 'text-info';
				} elseif ($track['has_richsync']) {
					$lyricType = 'Word by Word';
					$color = 'text-success';
				} else {
					$lyricType = 'No Lyrics';
					$color = 'text-danger';
				}
				$art = !empty($track['album_coverart_800x800'])
					? $track['album_coverart_800x800']
					: (!empty($track['album_coverart_500x500'])
						? $track['album_coverart_500x500']
						: (!empty($track['album_coverart_350x350'])
							? $track['album_coverart_350x350']
							: (!empty($track['album_coverart_100x100'])
								? $track['album_coverart_100x100']
								: 'https://placehold.co/500?text=' .
									urlencode($track['album_name']))));
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $track['track_name'] }}">
					<div class="card-header">
						{{ $track['artist_name'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $track['track_name'] . ($track['explicit'] ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $track['album_name'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							{{ date_format(date_create($track['first_release_date']), 'j F Y') }}
						</li>
						<li class="list-group-item">
							<span class="{{ $color }}"><b>{{ $lyricType }}</b></span>
						</li>
					</ul>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<div class="btn-group" role="group">
								<button type="button" class="btn btn-primary dropdown-toggle"
									data-bs-toggle="dropdown" aria-expanded="false"
									@if ($track['instrumental'] || !$track['has_lyrics']) aria-disabled="true" disabled @endif>
									<i class="fa-solid fa-download"></i>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a @class([
											'dropdown-item',
											'download-btn',
											'disabled' => !$track['has_lyrics'],
										]) href="#"
											data-id="{{ $track['commontrack_id'] }}" data-type="lyrics"
											data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}"
											data-title="{{ $track['track_name'] }}">
											Plain
										</a>
									</li>
									<li>
										<a @class([
											'dropdown-item',
											'download-btn',
											'disabled' => !$track['has_subtitles'],
										]) href="#"
											data-id="{{ $track['commontrack_id'] }}" data-type="subtitle"
											data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}"
											data-title="{{ $track['track_name'] }}">
											Synced
										</a>
									</li>
									<li>
										<a @class([
											'dropdown-item',
											'download-btn',
											'disabled' => !$track['has_richsync'],
										]) href="#"
											data-id="{{ $track['commontrack_id'] }}" data-type="richsync"
											data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}"
											data-title="{{ $track['track_name'] }}">
											Word-by-Word
										</a>
									</li>
								</ul>
							</div>
							<a href="{{ $track['track_share_url'] }}" @class(['btn', 'btn-info', 'disabled' => !$track['has_lyrics']])
								@if (!$track['has_lyrics']) aria-disabled="true" @endif target="_blank">
								<i class="fa-solid fa-eye"></i>
							</a>
							<a href="https://open.spotify.com/track/{{ $track['track_spotify_id'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($track['track_spotify_id']),
								])
								@empty($track['track_spotify_id']) aria-disabled="true" @endempty target="_blank">
								<i class="fa-brands fa-spotify"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	@if (!request()->routeIs('musixmatch.chart'))
		@php
			$curRoute = request()->route()->getName();
			$queries=[
				'prev'=>['page' => request('page') === null ? 1 : request('page') - 1],
				'next'=>['page' => (request('page') ?? 1) + 1]
			];
			if (request('query')) {
				$queries['prev']['query'] = request('query');
				$queries['next']['query'] = request('query');
			} else {
				$queries['prev']['title'] = request('title') ?? '';
				$queries['prev']['artist'] = request('artist') ?? '';
				$queries['prev']['album']= request('album') ?? '';
				$queries['next']['title'] = request('title') ?? '';
				$queries['next']['artist'] = request('artist') ?? '';
				$queries['next']['album']= request('album') ?? '';
			}
		@endphp
		<div class="mx-5 px-5 mb-5 pb-5">
			<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
				<ul class="pagination justify-content-center">
					{{-- Previous Page Link --}}
					@if (request('page') === null || request('page') == 1)
						<li class="page-item disabled" aria-disabled="true">
							<span class="page-link">{!! __('pagination.previous') !!}</span>
						</li>
					@else
						<li class="page-item">
							<a class="page-link" rel="prev"
								href="{{ route($curRoute, $queries['prev']) }}">
								{!! __('pagination.previous') !!}
							</a>
						</li>
					@endif
					{{-- Next Page Link --}}
					@if (20 * (request('page') ?? 1) < $header['available'])
						<li class="page-item">
							<a class="page-link" rel="next"
								href="{{ route($curRoute, $queries['next']) }}">
								{!! __('pagination.next') !!}
							</a>
						</li>
					@else
						<li class="page-item disabled" aria-disabled="true">
							<span class="page-link">{!! __('pagination.next') !!}</span>
						</li>
					@endif
				</ul>
			</nav>
		</div>
	@endif
@endif
