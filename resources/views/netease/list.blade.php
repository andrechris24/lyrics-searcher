@if ($data['songCount'] > 0)
	<p class="text-center">Showing {{ (request('offset') ?? 0) + 1 }} to
		{{ (request('offset') ?? 0) + 20 > $data['songCount'] ? $data['songCount'] : request('offset') + 20 }}
		of {{ $data['songCount'] }} result(s)</p>
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@foreach ($data['songs'] as $result)
			@php
				$artists = [];
				$length = gmdate('i:s', round($result['duration'] / 1000, 0, PHP_ROUND_HALF_UP));
				foreach ($result['artists'] as $ar) {
					$artists[] = $ar['name'];
				}
			@endphp
			<a class="list-group-item list-group-item-action" data-coreui-toggle="modal"
				data-coreui-album="{{ $result['album']['name'] }}"
				data-coreui-duration="{{ $length }}" data-coreui-title="{{ $result['name'] }}"
				data-coreui-id="{{ $result['id'] }}" data-coreui-artist="{{ implode(', ', $artists) }}"
				href="#modalLyrics">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['name'] }}</h5>
					<small>{{ $length }}</small>
				</div>
				<p class="mb-1">{{ implode(', ', $artists) }}</p>
				<small>{{ $result['album']['name'] }}</small>
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
						<a class="page-link" rel="prev" href="javascript:"
							onclick="navigate('{{ request('query') }}',{{ request('offset') - 20 }})">
							{!! __('pagination.previous') !!}
						</a>
					@endif
				</li>
				@php($nextOffset = 20 + (request('offset') ?? 0))
				<li @class([
					'page-item',
					'disabled' => !($nextOffset < $data['songCount'])
				])
					aria-disabled="{{ !($nextOffset < $data['songCount']) }}">
					@if ($nextOffset < $data['songCount'])
						<a class="page-link" rel="next" href="javascript:"
							onclick="navigate('{{ request('query') }}',{{ (request('offset') ?? 0) + 20 }})">
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
	<x-no-results source="netease" />
@endif
