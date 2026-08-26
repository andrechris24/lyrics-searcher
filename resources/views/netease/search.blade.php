@extends('layout')
@section('title', 'NetEase Music Search')
@section('subpage-title', 'NetEase Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="netease" />
	</div>
	<div class="modal fade" tabindex="-1" id="modalLyrics" aria-labelledby="modalLyricsLabel"
		role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLyricsLabel" class="modal-title">Preview lyric</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
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
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button"
							data-coreui-toggle="dropdown" aria-expanded="false">
							Save
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="dropdown-item" href="#" id="dl-synced" data-coreui-toggle="tooltip"
									data-coreui-title="With format as shown in content">Save</a>
							</li>
							<li>
								<a class="dropdown-item" href="#" id="dl-klyric">Word by word</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	@include('netease.skeleton')
	<div id="netease-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/netease.js') }}"></script>
@endsection
