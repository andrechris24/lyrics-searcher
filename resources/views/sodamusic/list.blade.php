@empty($data['data'])
	<x-no-results source="sodamusic" />
@else
	<p class="text-center">Showing {{ (request('offset') ?? 0) + 1 }} to
		{{ $data['next_cursor'] }} result(s). Click on a list to save.</p>
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@foreach ($data['data'] as $result)
			@php
				$artists = [];
				$res = $result['entity']['track'];
				$length = gmdate('i:s', round($res['duration'] / 1000, 0, PHP_ROUND_HALF_UP));
				foreach ($res['artists'] as $ar) {
					$artists[] = $ar['name'];
				}
			@endphp
			<a class="list-group-item list-group-item-action" data-duration="{{ $length }}"
				data-album="{{ $res['album']['name'] }}" data-title="{{ $res['name'] }}"
				data-id="{{ $res['id'] }}" data-artist="{{ implode(', ', $artists) }}" href="#">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $res['name'] }}</h5>
					<small>{{ $length }}</small>
				</div>
				<p class="mb-1">{{ implode(', ', $artists) }}</p>
				<small>{{ $res['album']['name'] }}</small>
			</a>
		@endforeach
	</div>
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				<li @class([
					'page-item',
					'disabled' => in_array(request('offset'), [0, null])
				])
					aria-disabled="{{ in_array(request('offset'), [0, null]) }}">
					@if (in_array(request('offset'), [0, null]))
						<span class="page-link">{!! __('pagination.previous') !!}</span>
					@else
						<a class="page-link" rel="prev"
							href="javascript:navigate('{{ request('query') }}',{{ request('offset') - 20 }})">
							{!! __('pagination.previous') !!}
						</a>
					@endif
				</li>
				<li @class(['page-item', 'disabled' => !$data['has_more']]) aria-disabled="{{ !$data['has_more'] }}">
					@if ($data['has_more'])
						<a class="page-link" rel="next"
							href="javascript:navigate('{{ request('query') }}',{{ $data['next_cursor'] }})">
							{!! __('pagination.next') !!}
						</a>
					@else
						<span class="page-link">{!! __('pagination.next') !!}</span>
					@endif
				</li>
			</ul>
		</nav>
	</div>
@endif
