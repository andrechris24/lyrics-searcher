@extends('layout')
@section('title', 'Spotify Search')
@section('subpage-title', 'Spotify Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="spotify" />
	</div>
	@include('spotify.skeleton')
	<div class="modal fade" tabindex="-1" id="modalMX" aria-labelledby="modalMXLabel"
		role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalMXLabel" class="modal-title">
						Preview Lyric
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
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
							<span id="song-copyright"></span>
						</div>
					</div>
					<p id="lyrics-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
					<a href="#" class="btn btn-warning" target="_blank" id="musixmatch-btn">
						<i class="fa-solid fa-music"></i> Musixmatch
					</a>
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button"
							data-coreui-toggle="dropdown" aria-expanded="false">
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
								<a class="dropdown-item" href="#" id="download-link-richsync">Richsync</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="spotify-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/spotify.js') }}"></script>
@endsection
