<x-no-script />
@empty($songinfo)
	<x-no-results source="qqmusic" />
@else
	<p class="text-center">
		Showing {{ $rangemin }} to {{ $rangemax <= $songcount ? $rangemax : $songcount }}
		of {{ $songcount }} result(s)</p>
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@if(!array_key_exists('@attributes',$songinfo))
			@foreach ($songinfo as $result)
				<a class="list-group-item list-group-item-action" href="#"
					data-title="{{ urldecode($result['name']) }}"
					data-artist="{{ urldecode($result['singername']) }}"
					data-id="{{ $result['@attributes']['id'] }}">
					<div class="d-flex w-100 justify-content-between">
						<h5 class="mb-1">{{ urldecode($result['name']) }}</h5>
					</div>
					<p class="mb-1">{{ urldecode($result['singername']) }}</p>
					<small>{{ urldecode($result['albumname']) }}</small>
				</a>
			@endforeach
		@else
			<a class="list-group-item list-group-item-action" href="#"
				data-title="{{ urldecode($songinfo['name']) }}"
				data-artist="{{ urldecode($songinfo['singername']) }}"
				data-id="{{ $songinfo['@attributes']['id'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ urldecode($songinfo['name']) }}</h5>
				</div>
				<p class="mb-1">{{ urldecode($songinfo['singername']) }}</p>
				<small>{{ urldecode($songinfo['albumname']) }}</small>
			</a>
		@endif
	</div>
	@php
		$curRoute = request()->route()->getName();
		$queries = [
			'prev' => [
				'title' => request('title'),
				'artist' => request('artist'),
				'offset' => $rangemin - 1
			],
			'next' => [
				'title' => request('title'),
				'artist' => request('artist'),
				'offset' => $rangemax
			]
		];
	@endphp
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				{{-- Previous Page Link --}}
				@if ($rangemin <= 1)
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
				@if ($rangemax < $songcount)
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
