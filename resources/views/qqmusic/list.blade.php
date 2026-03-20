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
				<div class="row mb-3">
					<div class="col-12 col-md-4">
						<b>Artist</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="song-artist">...</span>
					</div>
					<div class="col-12 col-md-4">
						<b>Title</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="song-title">...</span>
					</div>
				</div>
				<div class="alert alert-danger d-none" id="error-alert">
					<span id="error-message"></span>
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
				<a class="btn btn-primary" href="#" id="save-btn">
					Save to Device
				</a>
			</div>
		</div>
	</div>
</div>
@if (count($data) > 0)
	<p class="text-center">Found {{ $data['count'] }} result(s)</p>
	<div class="list-group mx-5 px-5 mb-5 pb-5">
		@foreach ($data['itemlist'] as $result)
			<a class="list-group-item list-group-item-action" href="#modalLyrics"
				data-bs-toggle="modal" data-bs-title="{{ $result['name'] }}"
				data-bs-artist="{{ $result['singer'] }}" data-bs-id="{{ $result['mid'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['name'] }}</h5>
				</div>
				<p class="mb-1">{{ $result['singer'] }}</p>
			</a>
		@endforeach
	</div>
@else
	<x-no-results source="qqmusic" />
@endif
