@empty($data)
	<x-no-results source="musixmatch" />
@else
	@empty($typeName)
		<p class="text-center">Page {{ request('page') ?? 1 }} out of
			{{ $header['available'] }} result(s), showing 20 results per page</p>
	@else
		<p class="text-center">Showing 20 {{ $typeName }} Charts of {{ $country }}.</p>
	@endempty
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			@php
				$track = $result['track'];
				$length = gmdate('i:s', $track['track_length']);
				if ($track['instrumental']) {
				    $lyricType = 'Instrumental';
				    $color = 'text-warning';
				} elseif ($track['has_richsync']) {
				    $lyricType = 'Richsync';
				    $color = 'text-success';
				} elseif ($track['has_subtitles']) {
				    $lyricType = 'Synced';
				    $color = 'text-primary';
				} elseif ($track['has_lyrics']) {
				    $lyricType = 'Plain';
				    $color = 'text-info';
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
				$unavailable = $track['instrumental'] || !$track['has_lyrics'];
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $track['track_name'] }}">
					<div class="card-header">
						{{ $track['album_name'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $track['track_name'] . ($track['explicit'] ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $track['artist_name'] }}</p>
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
									data-coreui-toggle="dropdown" aria-expanded="false"
									aria-disabled="{{ $unavailable }}" @disabled($unavailable)>
									<i class="fa-solid fa-download"></i>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a @class([
											'dropdown-item',
											'download-btn',
											'disabled' => !$track['has_lyrics']
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
											'disabled' => !$track['has_subtitles']
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
											'disabled' => !$track['has_richsync']
										]) href="#"
											title="Not all players support Richsync, use with compatible players like MiniLyrics or ESLyric"
											data-id="{{ $track['commontrack_id'] }}" data-type="richsync"
											data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}"
											data-title="{{ $track['track_name'] }}" data-coreui-toggle="tooltip">
											Richsync
										</a>
									</li>
								</ul>
							</div>
							<a href="{{ $track['track_share_url'] }}" @class(['btn', 'btn-info', 'disabled' => !$track['has_lyrics']])
								aria-disabled="{{ !$track['has_lyrics'] }}" target="_blank"
								data-coreui-toggle="tooltip" data-coreui-title="View Lyric on Musixmatch">
								<i class="fa-solid fa-eye"></i>
							</a>
							<a href="https://open.spotify.com/track/{{ $track['track_spotify_id'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($track['track_spotify_id'])
								])
								aria-disabled="{{ empty($track['track_spotify_id']) }}"
								data-coreui-toggle="tooltip" data-coreui-title="View on Spotify" target="_blank">
								<i class="fa-brands fa-spotify"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	@empty($typeName)
		@php
			$queries = [
			    'prev' => ['page' => request('page') === null ? 1 : request('page') - 1],
			    'next' => ['page' => (request('page') ?? 1) + 1]
			];
			if (request('query')) {
			    $queries['prev']['query'] = request('query');
			    $queries['prev']['type'] = request('type');
			    $queries['next']['query'] = request('query');
			    $queries['next']['type'] = request('type');
			} else {
			    $queries['prev']['title'] = request('title') ?? '';
			    $queries['prev']['artist'] = request('artist') ?? '';
			    $queries['prev']['lyrics'] = request('lyrics') ?? '';
			    $queries['next']['title'] = request('title') ?? '';
			    $queries['next']['artist'] = request('artist') ?? '';
			    $queries['next']['lyrics'] = request('lyrics') ?? '';
			}
		@endphp
		<div class="mx-5 px-5 mb-5 pb-5">
			<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
				<ul class="pagination justify-content-center">
					<li @class([
						'page-item',
						'disabled' => in_array(request('page'), [1, null])
					])
						aria-disabled="{{ in_array(request('page'), [1, null]) }}">
						@if (in_array(request('page'), [1, null]))
							<span class="page-link">{!! __('pagination.previous') !!}</span>
						@else
							<a class="page-link" rel="prev"
								href="javascript:navigate('{{ request()->url() }}',{{ json_encode($queries['prev']) }})">
								{!! __('pagination.previous') !!}
							</a>
						@endif
					</li>
					@php($nextOffset = 20 * (request('page') ?? 1))
					<li @class([
						'page-item',
						'disabled' => $nextOffset >= $header['available']
					])
						aria-disabled="{{ $nextOffset >= $header['available'] }}">
						@if ($nextOffset < $header['available'])
							<a class="page-link" rel="next"
								href="javascript:navigate('{{ request()->url() }}',{{ json_encode($queries['next']) }})">
								{!! __('pagination.next') !!}
							</a>
						@else
							<span class="page-link">{!! __('pagination.next') !!}</span>
						@endif
					</li>
				</ul>
			</nav>
		</div>
	@endempty
	@endif
