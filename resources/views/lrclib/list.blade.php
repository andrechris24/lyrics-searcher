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
					<div class="col-12 col-md-4">
						<b>Album</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="song-album">-</span>
					</div>
					<div class="col-12 col-md-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="song-duration">--:--</span>
					</div>
				</div>
				<p id="plain-lyrics-content" style="white-space: pre-line"></p>
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
							<a class="dropdown-item" href="#" id="download-link-plain">Plain</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" id="download-link-synced">Synced</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<x-no-script />
@empty($data)
	<x-no-results source="lrclib" />
@else
	<p class="text-center">Only first 20 results are returned due to API limitation</p>
	<div class="list-group mx-5 px-5 mb-5 pb-5">
		@foreach ($data as $result)
			@php($length = gmdate('i:s', $result['duration']))
			<a class="list-group-item list-group-item-action"
				@if (!$result['instrumental']) data-bs-toggle="modal" data-bs-album="{{ $result['albumName'] }}" data-bs-duration="{{ $length }}"
				data-bs-title="{{ $result['trackName'] }}" data-bs-artist="{{ $result['artistName'] }}"
				data-bs-plain="{{ $result['plainLyrics'] }}" data-bs-id="{{$result['id']}}"
				data-bs-synced="{{ $result['syncedLyrics'] }}" @else onclick="toast.fire({icon: 'info',text: 'This song is Instrumental'});return false;" @endif
				href="#modalLyrics">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['trackName'] }}</h5>
					<small>{{ $length }} |
						@if ($result['instrumental'])
							<span class="text-info">Instrumental</span>
						@elseif(!empty($result['syncedLyrics']))
							<span class="text-success">Synced</span>
						@else
							<span class="text-secondary">Plain</span>
						@endif
					</small>
				</div>
				<p class="mb-1">{{ $result['artistName'] }}</p>
				<small>{{ $result['albumName'] }}</small>
			</a>
		@endforeach
	</div>
	@endif
