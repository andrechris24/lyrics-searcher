@extends('layout')
@section('title', 'Local Search')
@section('subpage-title', 'Local Search')
@section('content')
	<x-upload-lyric />
	<div class="modal fade" tabindex="-1" id="modalLocalFile" role="dialog"
		aria-labelledby="modalLocalFileLabel" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLocalFileLabel" class="modal-title">View Lyric</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-sm-4">
							<b>Artist</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-song-artist">...</span>
						</div>
						<div class="col-12 col-sm-4">
							<b>Title</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-song-title">...</span>
						</div>
						<div class="col-12 col-sm-4">
							<b>Album</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-song-album">-</span>
						</div>
						<div class="col-12 col-sm-4">
							<b>Duration</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-song-duration">--:--</span>
						</div>
						<div class="col-12 col-sm-4">
							<b>Uploader</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-uploader">Guest</span>
						</div>
						<div class="col-12 col-sm-4">
							<b>Upload Date</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-song-upload"></span>
						</div>
						<div class="col-12 col-sm-4">
							<b>Last Update</b>
						</div>
						<div class="col-12 col-sm-8">
							<span id="local-song-update"><span>
						</div>
					</div>
					<p id="local-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						Close
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="mb-5">
		@auth(backpack_guard_name())
			<button type="button" class="btn btn-success" data-bs-toggle="modal"
				data-bs-target="#modalUploadLyric">Upload lyric</button>
		@endauth
		<table id="local-lyrics" class="table table-striped">
			<thead>
				<th>Title</th>
				<th>Artist</th>
				<th>Album</th>
				<th>Duration</th>
				<th>By</th>
				<th>Upload Date</th>
				<th>Action</th>
			</thead>
		</table>
	</div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/localserver.js') }}"></script>
@endsection
