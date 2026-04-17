<x-no-script />
@empty($body['song']['list'])
	<x-no-results source="qqmusic" />
@else
	<p class="text-center">Page {{ request('page') ?? 1 }} of {{ $meta['sum'] }} result(s),
		showing {{ $meta['perpage'] }} per page.</p>
	<div class="list-group mx-5 px-5 mb-5 pb-5">
		@foreach ($body['song']['list'] as $result)
			@php
				$artists = [];
				foreach ($result['singer'] as $ar) {
					$artists[] = $ar['name'];
				}
			@endphp
			<a class="list-group-item list-group-item-action" href="#"
				data-title="{{ $result['name'] }}" data-artist="{{ implode(', ', $artists) }}"
				data-id="{{ $result['mid'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['name'] }}</h5>
				</div>
				<p class="mb-1">{{ implode(', ', $artists) }}</p>
				<small>{{ $result['album']['name'] }}</small>
			</a>
		@endforeach
	</div>
	@php
		$curRoute = request()->route()->getName();
		$queries = [
			'prev' => ['query' => request('query'),'page' => $meta['curpage'] - 1],
			'next' => ['query' => request('query'),'page' => $meta['nextpage']]
		];
	@endphp
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				{{-- Previous Page Link --}}
				@if ($meta['curpage'] <= 1)
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
				@if (!in_array($meta['nextpage'], [null, -1]))
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
@endempty
