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
						<b>Lyric Type</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-lyric-type">...</span>
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
						<li>
							<a class="dropdown-item" href="#" id="dl-ttml">TTML</a>
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
@if ($resultCount > 0)
	<p class="text-center">Found {{ $resultCount }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($results as $result)
			@php
				$length = gmdate(
					'i:s', 
					round($result['trackTimeMillis'] / 1000, 0, PHP_ROUND_HALF_UP)
				);
				$art = !empty($result['artworkUrl100'])
					? $result['artworkUrl100']
					: (!empty($result['artworkUrl60'])
						? $result['artworkUrl60']
						: (!empty($result['artworkUrl30'])
							? $result['artworkUrl30']
							: 'https://placehold.co/500?text=' .
									urlencode($result['album']['name'])));
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $result['trackName'] }}">
					<div class="card-header">
						{{ $result['collectionName'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['trackName'] . ($result['trackExplicitness']==='Explicit' ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artistName'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal"
								data-bs-target="#modalLyrics" data-bs-id="{{ $result['trackId'] }}"
								data-bs-artist="{{ $result['artistName'] }}"
								data-bs-title="{{ $result['trackName'] }}"
								data-bs-album="{{ $result['collectionName'] }}"
								data-bs-duration="{{ $length }}">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button type="button" @class(['btn', 'btn-info', 'disabled' => empty($result['previewUrl'])])
								@if (empty($result['previewUrl'])) aria-disabled="true" @endif
								data-bs-link="{{ $result['previewUrl'] }}"
								data-bs-artist="{{ $result['artistName'] }}"
								data-bs-title="{{ $result['trackName'] }}"
								data-bs-album="{{ $result['collectionName'] }}"
								data-bs-duration="{{ $length }}"
								data-bs-toggle="modal" data-bs-target="#modalPreviewSong">
								<i class="fa-solid fa-play"></i>
							</button>
							<a href="{{ $result['trackViewUrl'] }}" @class(['btn', 'btn-success', 'disabled' => empty($result['trackViewUrl'])])
								@empty($result['trackViewUrl']) aria-disabled="true" @endempty
								data-bs-toggle="tooltip" data-bs-title="View on Apple Music" target="_blank">
								<i class="fa-brands fa-itunes-note"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<x-no-results source="apple" />
@endif
