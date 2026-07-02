<div class="modal fade" tabindex="-1" id="modalLyrics" aria-labelledby="modalLyricsLabel"
	role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalLyricsLabel" class="modal-title">Preview lyric</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<x-lyrically/>
				<div class="row mb-3">
					<div class="col-12 col-sm-4">
						<b>Artist</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-artist">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Title</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-title">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Album</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-album">-</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-duration">--:--</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Writers</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-writers">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Copyright</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-copyright">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>License</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-license">...</span>
					</div>
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
						data-bs-toggle="dropdown" aria-expanded="false">
						Save
					</button>
					<ul class="dropdown-menu">
						<li>
							<a class="dropdown-item" href="#" id="dl-plain">Plain</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" id="dl-synced">Synced</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" id="dl-syllyric">Syllable</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modalPreviewSong" aria-labelledby="modalPreviewSongLabel" role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalPreviewSongLabel" class="modal-title">Preview Song</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-12 col-sm-4">
						<b>Artist</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-artist">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Title</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-title">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Album</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-album">-</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-duration">--:--</span>
					</div>
				</div>
				<audio controls id="preview-player">
					<source id="preview-song" src="">
					Your browser does not support the audio element.
				</audio>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					Close
				</button>
			</div>
		</div>
	</div>
</div>
<x-no-script />
@if ($total > 0)
	<p class="text-center">Showing {{ (request('offset') ?? 0) + 1 }} to
		{{ (request('offset') ?? 0) + 20 > $total ? $total : request('offset') + 20 }}
		of {{ $total }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			@php
				$length = gmdate('i:s', $result['duration']);
				$art = !empty($result['album']['cover_xl'])
					? $result['album']['cover_xl']
					: (!empty($result['album']['cover_big'])
						? $result['album']['cover_big']
						: (!empty($result['album']['cover_medium'])
							? $result['album']['cover_medium']
							: (!empty($result['album']['cover_small'])
								? $result['album']['cover_small']
								: 'https://placehold.co/500?text=' .
									urlencode($result['album']['name']))));
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $result['title'] }}">
					<div class="card-header">
						{{ $result['album']['title'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['title'] . ($result['explicit_lyrics'] ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artist']['name'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal"
								data-bs-target="#modalLyrics" data-bs-id="{{ $result['id'] }}"
								data-bs-artist="{{ $result['artist']['name'] }}"
								data-bs-title="{{ $result['title'] }}"
								data-bs-album="{{ $result['album']['title'] }}"
								data-bs-duration="{{ $length }}">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button type="button" @class(['btn', 'btn-info', 'disabled' => empty($result['preview'])])
								@if (empty($result['preview'])) aria-disabled="true" @endif
								data-bs-link="{{ $result['preview'] }}"
								data-bs-artist="{{ $result['artist']['name'] }}"
								data-bs-title="{{ $result['title'] }}"
								data-bs-album="{{ $result['album']['title'] }}"
								data-bs-duration="{{ $length }}"
								data-bs-toggle="modal" data-bs-target="#modalPreviewSong">
								<i class="fa-solid fa-play"></i>
							</button>
							<a href="{{ $result['link'] }}" @class(['btn', 'btn-success', 'disabled' => empty($result['link'])])
								@empty($result['link']) aria-disabled="true" @endempty
								data-bs-toggle="tooltip" data-bs-title="View on Deezer" target="_blank">
								<i class="fa-brands fa-deezer"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				{{-- Previous Page Link --}}
				@if (empty($prev))
					<li class="page-item disabled" aria-disabled="true">
						<span class="page-link">{!! __('pagination.previous') !!}</span>
					</li>
				@else
					<li class="page-item">
						<a class="page-link" rel="prev"
							href="{{ route('deezer.search', ['query' => request('query'), 'offset' => request('offset') - 20]) }}">
							{!! __('pagination.previous') !!}
						</a>
					</li>
				@endif

				{{-- Next Page Link --}}
				@if (!empty($next))
					<li class="page-item">
						<a class="page-link" rel="next"
							href="{{ route('netease.search', ['query' => request('query'), 'offset' => (request('offset') ?? 0) + 20]) }}">{!! __('pagination.next') !!}</a>
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
	<x-no-results source="deezer" />
@endif
