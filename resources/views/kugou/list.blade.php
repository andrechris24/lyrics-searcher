<div class="modal fade" tabindex="-1" id="modalLyrics" aria-labelledby="modalLyricsLabel"
	role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalLyricsLabel" class="modal-title">
					Select lyrics for <span id="lrc-query">...</span>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
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
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					Close
				</button>
			</div>
		</div>
	</div>
</div>
<x-no-script />
@if ($data['total'] > 0)
	<p class="text-center">Page {{ request('page') ?? 1 }} out of {{ $data['total'] }}
		result(s), showing 20 results per page</p>
	<div class="list-group mx-5 px-5 mb-5 pb-5">
		@foreach ($data['info'] as $result)
			<a class="list-group-item list-group-item-action" data-bs-toggle="modal"
				href="#modalLyrics" data-bs-query="{{ $result['filename'] }}"
				data-bs-hash="{{ $result['hash'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['songname'] }}</h5>
				</div>
				<p class="mb-1">{{ $result['singername'] }}</p>
				<small>{{ $result['album_name'] }}</small>
			</a>
		@endforeach
	</div>
	@php
		$curRoute = request()->route()->getName();
		$queries = [
		    'prev' => [
		        'title' => request('title') ?? '',
		        'artist' => request('artist') ?? '',
		        'page' => request('page') === null ? 1 : request('page') - 1,
		    ],
		    'next' => [
		        'title' => request('title') ?? '',
		        'artist' => request('artist') ?? '',
		        'page' => (request('page') ?? 1) + 1,
		    ],
		];
	@endphp
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				{{-- Previous Page Link --}}
				@if (request('page') === null || request('page') == 1)
					<li class="page-item disabled" aria-disabled="true">
						<span class="page-link">{!! __('pagination.previous') !!}</span>
					</li>
				@else
					<li class="page-item">
						<a class="page-link" rel="prev" href="{{ route($curRoute, $queries['prev']) }}">
							{!! __('pagination.previous') !!}
						</a>
					</li>
				@endif
				{{-- Next Page Link --}}
				@if (20 * (request('page') ?? 1) < $data['total'])
					<li class="page-item">
						<a class="page-link" rel="next" href="{{ route($curRoute, $queries['next']) }}">
							{!! __('pagination.next') !!}
						</a>
					</li>
				@else
					<li class="page-item disabled" aria-disabled="true">
						<span class="page-link">{!! __('pagination.next') !!}</span>
					</li>
				@endif
			</ul>
		</nav>
	</div>
@else
	<x-no-results source="kugou" />
@endif
