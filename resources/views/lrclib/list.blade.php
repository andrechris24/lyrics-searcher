@empty($data)
	<x-no-results source="lrclib" />
@else
	@if (count($data) === 20)
		<div class="callout callout-warning">
			<div class="text-center">Due to API limitation, only first 20 results are
				returned.</div>
		</div>
	@else
		<p class="text-center">Found {{ count($data) }} result(s)</p>
	@endif
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@foreach ($data as $result)
			@php
			$length = gmdate('i:s', $result['duration']);
			$wbw=\Illuminate\Support\Str::contains($result['lyricsfile'],'words:',ignoreCase: true);
			@endphp
			<a class="list-group-item list-group-item-action"
				@if (!$result['instrumental']) data-coreui-toggle="modal" data-coreui-album="{{ $result['albumName'] }}" data-coreui-duration="{{ $length }}"
				data-coreui-title="{{ $result['trackName'] }}" data-coreui-artist="{{ $result['artistName'] }}"
				data-coreui-plain="{{ $result['plainLyrics'] }}" data-coreui-id="{{ $result['id'] }}"
				data-coreui-synced="{{ $result['syncedLyrics'] }}"
				data-coreui-wordbyword="{{$result['lyricsfile'] }}"
				@else onclick="toast.fire({icon: 'info',text: 'This song is Instrumental'});return false;" @endif
				href="#modalLRCLib">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['trackName'] }}</h5>
					<small>{{ $length }} |
						@if ($result['instrumental'])
							<span class="text-info">Instrumental</span>
						@elseif($wbw)
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
