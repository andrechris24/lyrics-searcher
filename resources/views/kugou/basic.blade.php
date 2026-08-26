@extends('layout')
@section('title', 'Kugou Music Search')
@section('subpage-title', 'Kugou Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="kugou" />
	</div>
	<div class="modal fade" tabindex="-1" id="modalLyrics" aria-labelledby="modalLyricsLabel"
		role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLyricsLabel" class="modal-title">
						Select lyrics for <span id="lrc-query">...</span>
					</h5>
					<button type="button" class="btn-close" data-coreui-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<table id="lyrics-table" class="table table-striped">
						<thead>
							<tr>
								<th>Artist</th>
								<th>Title</th>
								<th>Duration</th>
								<th>#</th>
							</tr>
						</thead>
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
						Close
					</button>
				</div>
			</div>
		</div>
	</div>
	@include('kugou.skeleton')
	<div id="kugou-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/kugou.js') }}"></script>
@endsection
