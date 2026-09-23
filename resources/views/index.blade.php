@extends('layout')
@section('title', 'Home')
@section('subpage-title', 'LRCSearch')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5">
		<p class="text-center">Welcome to LRCSearch! This site provides lyrics search from
			Kugou, NetEase, QQ Music, Musixmatch, LRCLib, Deezer, Apple Music,
			Amazon Music, plus optionally local server and plain sources.
			This form below is a quick search to 6 providers.</p>
		<form class="row g-3 mb-3" action="#" id="searchSongLyric">
			<div class="col-12 col-md-8">
				<div class="input-group input-group-lg">
					<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
					<div class="form-floating">
						<input type="text" class="form-control" id="track-name" placeholder="Song title"
							name="title" required autofocus>
						<label for="track-name" class="form-label">
							Song Title <span class="text-danger"><b>*</b></span>
						</label>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-4">
				<div class="form-floating">
					<select class="form-select form-select-lg" name="source" id="lyric-source" required>
						<option value="" selected>Choose</option>
						<option value="musixmatch">Musixmatch</option>
						<option value="lrclib">LRCLib</option>
						<option value="plains">Lyrics.ovh</option>
						<option value="genius">Genius</option>
						<option value="youtube">YouTube</option>
						<option value="local">Local</option>
					</select>
					<label for="lyric-source" class="form-label">
						Source <span class="text-danger"><b>*</b></span>
					</label>
				</div>
			</div>
			<div class="col-12 col-sm-6">
				<div class="input-group input-group-lg">
					<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
					<div class="form-floating">
						<input type="text" class="form-control" id="artist-name" placeholder="Artist"
							name="artist" required>
						<label for="artist-name" class="form-label">
							Artist <span class="text-danger"><b>*</b></span>
						</label>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6">
				<div class="input-group input-group-lg">
					<span class="input-group-text">
						<i class="fa-solid fa-compact-disc"></i>
					</span>
					<div class="form-floating">
						<input type="text" class="form-control" id="album-name" placeholder="Album"
							name="album">
						<label for="album-name" class="form-label">Album</label>
					</div>
				</div>
				<div class="form-text">Unused for Lyrics.ovh, Genius, & YouTube.</div>
			</div>
			<button type="submit" class="btn btn-primary">Search</button>
		</form>
	</div>
	<div class="modal fade" tabindex="-1" id="modalMX" aria-labelledby="modalMXLabel"
		role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalMXLabel" class="modal-title">
						Musixmatch Result for <span class="search-term">...</span>
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-md-4 mb-md-0 mb-2">
							<img src="" class="img-fluid" id="song-art">
						</div>
						<div class="col-12 col-md-8">
							<div class="row mb-2">
								<div class="col-4"><b>Title</b></div>
								<div class="col-8"><span id="mx-song-title">...</span></div>
								<div class="col-4"><b>Artist</b></div>
								<div class="col-8"><span id="mx-song-artist">...</span></div>
								<div class="col-4"><b>Album</b></div>
								<div class="col-8"><span id="mx-song-album">-</span></div>
								<div class="col-4"><b>Duration</b></div>
								<div class="col-8"><span id="mx-song-duration"></span></div>
								<div class="col-4"><b>Released</b></div>
								<div class="col-8"><span id="song-release-date"></span></div>
								<div class="col-4"><b>Last Update</b></div>
								<div class="col-8"><span id="song-last-update"></span></div>
								<div class="col-4"><b>Lyric Type</b></div>
								<div class="col-8"><span id="mx-lyric-type"></span></div>
							</div>
							<p id="song-copyright"></p>
							<div class="btn-group" role="group">
								<a href="#" class="btn btn-success" target="_blank" id="spotify-btn">
									<i class="fa-brands fa-spotify"></i> Spotify
								</a>
								<a href="#" class="btn btn-warning" target="_blank" id="musixmatch-btn">
									<i class="fa-solid fa-music"></i> Musixmatch
								</a>
							</div>
						</div>
					</div>
					<p id="mx-plain-lyrics-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button"
							data-coreui-toggle="dropdown" aria-expanded="false">
							Save to Device
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="dropdown-item" href="#" id="download-link-mx-plain">Plain</a>
							</li>
							<li>
								<a class="dropdown-item" href="#" id="download-link-mx-synced">Synced</a>
							</li>
							<li>
								<a class="dropdown-item" href="#"
									id="download-link-mx-richsync">Richsync</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<x-lrclib-modal text="LRCLib Result for " />
	<div class="modal fade" tabindex="-1" id="modalLyricsOVH"
		aria-labelledby="modalLyricsOVHLabel" role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLyricsOVHLabel" class="modal-title">
						Lyrics.ovh Result for <span class="search-term">...</span>
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						Lyrics.ovh returns lyrics from either Genius, AZLyrics, Paroles.net,
						LyricsMania, Letras.mus.br, and Lyrics.com in plain format only,
						without artist, title, and album information.
					</div>
					<p id="lyrics-ovh-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" tabindex="-1" id="modalLocal" role="dialog"
		aria-labelledby="modalLocalLabel" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLocalLabel" class="modal-title">
						Local search results for <span class="search-term"></span>
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-md-4">
							<b>Artist</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-artist">...</span>
						</div>
						<div class="col-12 col-md-4">
							<b>Title</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-title">...</span>
						</div>
						<div class="col-12 col-md-4">
							<b>Album</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-album">-</span>
						</div>
						<div class="col-12 col-md-4">
							<b>Duration</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-duration">--:--</span>
						</div>
						<div class="col-12 col-md-4">
							<b>By</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="lyric-by">?</span>
						</div>
					</div>
					<p id="local-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
					<button class="btn btn-primary" type="button" id="download-link-local">
						Save
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" tabindex="-1" id="modalGenius"
		aria-labelledby="modalGeniusLabel" role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalGeniusLabel" class="modal-title">
						Genius Result for <span class="search-term">...</span>
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-md-4 mb-md-0 mb-2">
							<img src="" class="img-fluid" id="genius-art">
						</div>
						<div class="col-12 col-md-8">
							<div class="callout callout-info">
								Genius only serves plain lyrics. Copy and paste them to save to
								your device.
							</div>
							<div class="row mb-2">
								<div class="col-4"><b>Title</b></div>
								<div class="col-8"><span id="genius-song-title">...</span></div>
								<div class="col-4"><b>Artist</b></div>
								<div class="col-8"><span id="genius-song-artist">...</span></div>
							</div>
						</div>
					</div>
					<p id="genius-lyrics-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
					<a href="#" class="btn btn-warning" target="_blank" id="genius-btn">
						<i class="fa-solid fa-music"></i> Genius
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" tabindex="-1" id="modalYouTube"
		aria-labelledby="modalYouTubeLabel" role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalYouTubeLabel" class="modal-title">
						YouTube Result for <span class="search-term">...</span>
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-md-4 mb-md-0 mb-2">
							<img src="" class="img-fluid" id="youtube-art">
						</div>
						<div class="col-12 col-md-8">
							<div class="callout callout-info">
								Due to API limitation, only plain lyrics are served.
							</div>
							<div class="row mb-2">
								<div class="col-4"><b>Title</b></div>
								<div class="col-8"><span id="youtube-song-title">...</span></div>
								<div class="col-4"><b>Artist</b></div>
								<div class="col-8"><span id="youtube-song-artist">...</span></div>
							</div>
						</div>
					</div>
					<p id="youtube-lyrics-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
					<a href="#" class="btn btn-danger" target="_blank" id="youtube-btn">
						<i class="fa-brands fa-youtube"></i> YouTube
					</a>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/quick.js') }}"></script>
@endsection
