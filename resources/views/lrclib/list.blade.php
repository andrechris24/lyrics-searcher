<x-lrclib-modal text="Preview lyric" />
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
				data-bs-plain="{{ $result['plainLyrics'] }}" data-bs-id="{{ $result['id'] }}"
				data-bs-synced="{{ $result['syncedLyrics'] }}" data-bs-wordbyword="{{ $result['lyricsfile'] }}"
				@else onclick="toast.fire({icon: 'info',text: 'This song is Instrumental'});return;" @endif
				href="#modalLRCLib">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['trackName'] }}</h5>
					<small>{{ $length }} |
						@if ($result['instrumental'])
							<span class="text-info">Instrumental</span>
						@elseif(!empty($result['lyricsfile']))
							<span class="text-primary">Word-by-Word</span>
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
