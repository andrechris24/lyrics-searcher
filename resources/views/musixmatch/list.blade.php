<div class="modal fade" tabindex="-1" id="modalMX" aria-labelledby="modalMXLabel"
	role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalMXLabel" class="modal-title">View Lyric</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-12 col-md-4">
						<img src="" class="img-fluid" id="song-art">
					</div>
					<div class="col-12 col-md-8">
						<div class="row mb-2">
							<div class="col-4"><b>Title</b></div>
							<div class="col-8"><span id="song-title">...</span></div>
							<div class="col-4"><b>Artist</b></div>
							<div class="col-8"><span id="song-artist">...</span></div>
							<div class="col-4"><b>Album</b></div>
							<div class="col-8"><span id="song-album">-</span></div>
							<div class="col-4"><b>Duration</b></div>
							<div class="col-8"><span id="song-duration"></span></div>
							<div class="col-4"><b>Last Update</b></div>
							<div class="col-8"><span id="song-last-update"></span></div>
							<div class="col"><p id="song-copyright"></p></div>
						</div>
					</div>
				</div>
				<div class="alert alert-info d-none" id="instrumental-message">
					This song is Instrumental
				</div>
				<div class="alert alert-warning d-none" id="no-lyrics-message">
					There are no lyrics available for this song
				</div>
				<p class="placeholder-glow d-none">
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
				</p>
				<p id="lyrics-content" style="white-space: pre-line"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					Close
				</button>
				<div class="dropdown">
					<button class="btn btn-primary dropdown-toggle" type="button"
						data-bs-toggle="dropdown" aria-expanded="false" id="save-button" disabled>
						Save to Device
					</button>
					<ul class="dropdown-menu">
						<li>
							<a class="dropdown-item" href="#" id="download-link-plain">Plain</a>
						</li>
						<li>
							<a class="dropdown-item disabled" href="#"
								id="download-link-synced">Synced</a>
						</li>
						<li>
							<a class="dropdown-item disabled" href="#" id="download-link-richsync">Word by Word</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
@if (count($data) > 0)
	<p class="text-center">Page {{request('page')??1}} out of {{ $header['available'] }} result(s), showing 20 results per page</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			@php
				$track = $result['track'];
				$length = gmdate('i:s', $track['track_length']);
				if ($track['instrumental']) {
						$lyricType = 'Instrumental';
						$color='text-warning';
				} elseif ($track['has_subtitles']) {
						$lyricType = 'Synced';
						$color='text-primary';
				} elseif ($track['has_lyrics']) {
						$lyricType = 'Plain';
						$color='text-info';
				} elseif ($track['has_richsync']) {
						$lyricType = 'Word by Word';
						$color="text-success";
				} else {
						$lyricType = 'No Lyrics';
						$color='text-danger';
				}
				$art = 
					(!empty($track['album_coverart_800x800'])
						? $track['album_coverart_800x800']
						: (!empty($track['album_coverart_500x500'])
							?$track['album_coverart_500x500']
							: (!empty($track['album_coverart_350x350'])
								? $track['album_coverart_350x350']
								: (!empty($track['album_coverart_100x100'])
									? $track['album_coverart_100x100'] : ''))));
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $track['track_name'] }}">
					<div class="card-header">
						{{ $track['artist_name'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">{{ $track['track_name'].($track['explicit']?' [E]':'') }}</h5>
						<p class="card-text">{{ $track['album_name'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							{{ date_format(date_create($track['first_release_date']), 'j F Y') }}
						</li>
						<li class="list-group-item">
						<span class="{{$color}}"><b>{{ $lyricType }}</b></span>
						</li>
					</ul>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<div class="btn-group" role="group">
								<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
								@if($track['instrumental']||!$track['has_lyrics']) aria-disabled="true" disabled
								@endif >
									<i class="fa-solid fa-download"></i>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a @class(["dropdown-item",'download-btn','disabled'=>!$track['has_lyrics']]) href="#" data-id="{{ $track['commontrack_id'] }}" data-type="lyrics" data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}" data-title="{{ $track['track_name'] }}">
										Plain
										</a>
									</li>
									<li>
										<a @class(["dropdown-item",'download-btn','disabled'=>!$track['has_subtitles']]) href="#" data-id="{{ $track['commontrack_id'] }}" data-type="subtitle" data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}" data-title="{{ $track['track_name'] }}">
										Synced
										</a>
									</li>
									<li>
										<a @class(["dropdown-item",'download-btn','disabled'=>!$track['has_richsync']]) href="#" data-id="{{ $track['commontrack_id'] }}" data-type="richsync" data-album="{{ $track['album_name'] }}"
											data-artist="{{ $track['artist_name'] }}" data-title="{{ $track['track_name'] }}">
										Word-by-Word
										</a>
									</li>
								</ul>
							</div>
							{{-- <button type="button" class="btn btn-info" data-bs-toggle="modal"
							data-bs-target="#modalMX" data-bs-art="{{ $art }}"
							data-bs-plain="{{ $track['has_lyrics'] }}"
							data-bs-synced="{{ $track['has_subtitles'] }}"
							data-bs-richsync="{{ $track['has_richsync'] }}"
							data-bs-explicit="{{ $track['explicit'] }}"
							data-bs-update="{{ date_format(date_create($track['updated_time']), 'j F Y') }}">
							</button> --}}
							<a href="{{ $track['track_share_url'] }}" @class(["btn", "btn-info", 'disabled'=>!$track['has_lyrics']]) @if(!$track['has_lyrics']) aria-disabled="true" @endif target="_blank">
								<i class="fa-solid fa-eye"></i>
							</a>
							<a href="https://open.spotify.com/track/{{ $track['track_spotify_id'] }}"
								@class(["btn", "btn-success", 'disabled'=>empty($track['track_spotify_id'])])
								@empty($track['track_spotify_id']) aria-disabled="true" @endempty target="_blank">
								<i class="fa-brands fa-spotify"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	@php
	$curRoute=request()->route()->getName();
	if(request('query')){
		$queries=[
			'prev'=>[
				'query'=>request('query'),
				'page'=>request('page')===null?1:request('page')-1
			],
			'next'=>['query'=>request('query'),'page'=>(request('page')??1)+1]
		];
	}else{
		$queries=[
			'prev'=>[
				'title'=>request('title')??'',
				'artist'=>request('artist')??'',
				'album'=>request('album')??'',
				'page'=>request('page')===null?1:request('page')-1
			],
			'next'=>[
				'title'=>request('title')??'',
				'artist'=>request('artist')??'',
				'album'=>request('album')??'',
				'page'=>(request('page')??1)+1
			]
		];
	}
	@endphp
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				{{-- Previous Page Link --}}
				@if (request('page') === null || request('page')==1)
					<li class="page-item disabled" aria-disabled="true">
						<span class="page-link">{!! __('pagination.previous') !!}</span>
					</li>
				@else
					<li class="page-item">
						<a class="page-link" rel="prev"	href="{{ route($curRoute, $queries['prev']) }}">
							{!! __('pagination.previous') !!}
						</a>
					</li>
				@endif
				{{-- Next Page Link --}}
				@if ((20*(request('page')??1))<$header['available'])
					<li class="page-item">
						<a class="page-link" rel="next"	href="{{ route($curRoute, $queries['next']) }}">
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
@else
	<x-no-results source="musixmatch" />
@endif
