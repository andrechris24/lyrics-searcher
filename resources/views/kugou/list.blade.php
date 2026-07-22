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
<x-no-script />
@if ($data['total'] > 0)
	<p class="text-center">Page {{ request('page') ?? 1 }} out of {{ $data['total'] }}
		result(s), showing 20 results per page</p>
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@foreach ($data['info'] as $result)
			<a class="list-group-item list-group-item-action" data-coreui-toggle="modal"
				href="#modalLyrics" data-coreui-hash="{{ $result['hash'] }}"
				data-coreui-query="{{ str_replace("\u{3001}", ', ', $result['filename']) }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['songname'] }}</h5>
					<small>{{ gmdate('i:s', $result['duration']) }}</small>
				</div>
				<p class="mb-1">{{ str_replace("\u{3001}", ', ', $result['singername']) }}</p>
				<small>{{ $result['album_name'] }}</small>
			</a>
		@endforeach
	</div>
	@php
		$curRoute = request()->route()->getName();
		$queries = [
			'prev' => [
				'query' => request('query'),
				'page' => request('page') === null ? 1 : request('page') - 1
			],
			'next' => [
				'query' => request('query'),
				'page' => (request('page') ?? 1) + 1
			]
		];
	@endphp
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				<li @class(["page-item", "disabled"=>in_array(request('page'), [1,null])])
					aria-disabled="{{in_array(request('page'), [1,null])}}">
					@if(in_array(request('page'), [1,null]))
						<span class="page-link">{!! __('pagination.previous') !!}</span>
					@else
						<a class="page-link" rel="prev" href="{{ route($curRoute, $queries['prev']) }}">
							{!! __('pagination.previous') !!}
						</a>
					@endif
				</li>
				@php($nextOffset=20 * (request('page') ?? 1))
				<li @class(["page-item",'disabled'=> $nextOffset >= $data['total']])
					aria-disabled="{{$nextOffset >= $data['total']}}">
					@if($nextOffset<$data['total'])
						<a class="page-link" rel="next" href="{{ route($curRoute, $queries['next']) }}">
							{!! __('pagination.next') !!}
						</a>
					@else
						<span class="page-link">{!! __('pagination.next') !!}</span>
					@endif
				</li>
			</ul>
		</nav>
	</div>
@else
	<x-no-results source="kugou" />
@endif
