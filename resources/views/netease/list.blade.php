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
				<a class="btn btn-primary" href="#" id="save-btn" disabled>
					Save to Device
				</a>
			</div>
		</div>
	</div>
</div>
@if ($data['songCount'] > 0)
	<p class="text-center">Showing {{ (request('offset')??0) + 1 }} to
		{{ (request('offset')??0) + 20 > $data['songCount'] ? $data['songCount'] : request('offset') + 20 }} of {{ $data['songCount'] }} result(s)</p>
	<div class="list-group mx-5 px-5 mb-5 pb-5">
		@foreach ($data['songs'] as $result)
			@php
				$artists = [];
				$length = gmdate('i:s', round($result['duration'] / 1000, 0, PHP_ROUND_HALF_UP));
				foreach ($result['artists'] as $ar) {
				    $artists[] = $ar['name'];
				}
			@endphp
			<a class="list-group-item list-group-item-action" data-bs-toggle="modal"
				data-bs-album="{{ $result['album']['name'] }}" data-bs-duration="{{ $length }}"
				data-bs-title="{{ $result['name'] }}" data-bs-id="{{ $result['id'] }}"
				data-bs-artist="{{ implode(', ', $artists) }}" href="#modalLyrics">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['name'] }}</h5>
					<small>{{ $length }}</small>
				</div>
				<p class="mb-1">{{ implode(', ', $artists) }}</p>
				<small>{{ $result['album']['name'] }}</small>
			</a>
		@endforeach
	</div>
	{{-- @if ((request('offset')??0)+20 < $data['songCount']) --}}
		<div class="mx-5 px-5 mb-5 pb-5">
			<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
				<ul class="pagination justify-content-center">
					{{-- Previous Page Link --}}
					@if (request('offset') === null || request('offset')==0)
						<li class="page-item disabled" aria-disabled="true">
							<span class="page-link">{!! __('pagination.previous') !!}</span>
						</li>
					@else
						<li class="page-item">
							<a class="page-link" rel="prev"
								href="{{ route('netease.search', ['query' => request('query'), 'offset' => request('offset')-20]) }}">
								{!! __('pagination.previous') !!}
							</a>
						</li>
					@endif

					{{-- Next Page Link --}}
					@if ((request('offset')??0)+20<$data['songCount'])
						<li class="page-item">
							<a class="page-link" rel="next"
								href="{{ route('netease.search', ['query' => request('query'), 'offset' => (request('offset')??0)+20]) }}">{!! __('pagination.next') !!}</a>
						</li>
					@else
						<li class="page-item disabled" aria-disabled="true">
							<span class="page-link">{!! __('pagination.next') !!}</span>
						</li>
					@endif
				</ul>
			</nav>
		</div>
	{{-- @endif --}}
@else
	<x-no-results source="netease" />
@endif
