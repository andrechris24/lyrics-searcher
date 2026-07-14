<x-no-script />
@if (count($data) > 0)
	<p class="text-center">Found {{ count($data) }} result(s)</p>
	<div class="modal fade" tabindex="-1" id="modalMX" aria-labelledby="modalMXLabel"
		role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalMXLabel" class="modal-title">
						Preview Lyric
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info mb-2">
						Spotify lyrics provided by Musixmatch
					</div>
					<div class="row mb-2">
						<div class="col-12 col-sm-4"><b>Title</b></div>
						<div class="col-12 col-sm-8"><span id="song-title">...</span></div>
						<div class="col-12 col-sm-4"><b>Artist</b></div>
						<div class="col-12 col-sm-8"><span id="song-artist">...</span></div>
						<div class="col-12 col-sm-4"><b>Album</b></div>
						<div class="col-12 col-sm-8"><span id="song-album">-</span></div>
						<div class="col-12 col-sm-4"><b>Duration</b></div>
						<div class="col-12 col-sm-8"><span id="song-duration"></span></div>
						<div class="col-12 col-sm-4"><b>Released</b></div>
						<div class="col-12 col-sm-8">
							<p class="placeholder-glow d-none">
								<span class="placeholder col-12"></span>
							</p>
							<span id="song-release-date"></span>
						</div>
						<div class="col-12 col-sm-4"><b>Last Update</b></div>
						<div class="col-12 col-sm-8">
							<p class="placeholder-glow d-none">
								<span class="placeholder col-12"></span>
							</p>
							<span id="song-last-update"></span>
						</div>
						<div class="col-12 col-sm-4"><b>Lyric Type</b></div>
						<div class="col-12 col-sm-8">
							<p class="placeholder-glow d-none">
								<span class="placeholder col-12"></span>
							</p>
							<span id="lyric-type"></span>
						</div>
						<div class="col-12 col-sm-4"><b>Copyright</b></div>
						<div class="col-12 col-sm-8">
							<p class="placeholder-glow d-none">
								<span class="placeholder col-12"></span>
							</p>
							<span id="song-sopyright"></span>
						</div>
					</div>
					<p id="lyrics-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						Close
					</button>
					<a href="#" class="btn btn-warning" target="_blank" id="musixmatch-btn">
						<i class="fa-solid fa-music"></i> Musixmatch
					</a>
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button"
							data-bs-toggle="dropdown" aria-expanded="false">
							Save to Device
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="dropdown-item" href="#" id="download-link-plain">Plain</a>
							</li>
							<li>
								<a class="dropdown-item" href="#" id="download-link-synced">Synced</a>
							</li>
							<li>
								<a class="dropdown-item" href="#"
									id="download-link-richsync">Richsync</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			<div class="col">
				<div class="card">
					<img src="{{ $result['albumCover'] }}" class="card-img-top"
						alt="{{ $result['albumName'] }}">
					<div class="card-header">
						{{ $result['albumName'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['name'] . ($result['contentRating'] === 'EXPLICIT' ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artistName'] }}</p>
						<small class="card-text text-muted">{{ $result['duration'] }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button class="btn btn-primary" data-bs-id="{{ $result['trackId'] }}"
								data-bs-artist="{{ $result['artistName'] }}" data-bs-title="{{ $result['name'] }}"
								data-bs-album="{{ $result['albumName'] }}"
								data-bs-duration="{{ $result['duration'] }}" data-bs-toggle="modal"
								data-bs-target="#modalMX">
								<i class="fa-solid fa-eye"></i>
							</button>
							<a href="https://open.spotify.com/track/{{ $result['trackId'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($result['trackId'])
								])
								@empty($result['trackId']) aria-disabled="true" @endempty
								data-bs-toggle="tooltip" data-bs-title="View on Spotify" target="_blank">
								<i class="fa-brands fa-spotify"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<x-no-results source="spotify" />
@endif
