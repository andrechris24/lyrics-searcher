@if ($total > 0)
	<p class="text-center">Showing {{ (request('offset') ?? 0) + 1 }} to
		{{ (request('offset') ?? 0) + 20 > $total ? $total : request('offset') + 20 }}
		of {{ $total }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			@php
				$length = gmdate('i:s', $result['duration']);
				$art = !empty($result['album']['cover_xl'])
					? $result['album']['cover_xl']
					: (!empty($result['album']['cover_big'])
						? $result['album']['cover_big']
						: (!empty($result['album']['cover_medium'])
							? $result['album']['cover_medium']
							: (!empty($result['album']['cover_small'])
								? $result['album']['cover_small']
								: 'https://placehold.co/500?text=' .
									urlencode($result['title']))));
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $result['title'] }}">
					<div class="card-header">
						{{ $result['album']['title'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['title'] . ($result['explicit_lyrics'] ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artist']['name'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-primary" data-coreui-toggle="modal"
								data-coreui-target="#modalLyrics" data-coreui-id="{{ $result['id'] }}"
								data-coreui-artist="{{ $result['artist']['name'] }}"
								data-coreui-title="{{ $result['title'] }}"
								data-coreui-album="{{ $result['album']['title'] }}"
								data-coreui-duration="{{ $length }}">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button type="button" @class(['btn', 'btn-info', 'disabled' => empty($result['preview'])])
								aria-disabled="{{ empty($result['preview']) }}"
								data-coreui-link="{{ $result['preview'] }}"
								data-coreui-artist="{{ $result['artist']['name'] }}"
								data-coreui-title="{{ $result['title'] }}"
								data-coreui-album="{{ $result['album']['title'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-target="#modalPreviewSong">
								<i class="fa-solid fa-play"></i>
							</button>
							<a href="{{ $result['link'] }}" @class(['btn', 'btn-success', 'disabled' => empty($result['link'])])
								aria-disabled="{{ empty($result['link']) }}" data-coreui-toggle="tooltip"
								data-coreui-title="View on Deezer" target="_blank">
								<i class="fa-brands fa-deezer"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	<div class="mx-5 px-5 mb-5 pb-5">
		<nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
			<ul class="pagination justify-content-center">
				<li @class(['page-item', 'disabled' => empty($prev)]) aria-disabled="{{ empty($prev) }}">
				@empty($prev)
					<span class="page-link">{!! __('pagination.previous') !!}</span>
				@else
					<a class="page-link" rel="prev"
						onclick="navigate('{{ request('query') }}',{{ request('offset') - 20 }})">
						{!! __('pagination.previous') !!}
					</a>
				@endempty
				</li>
				<li @class(['page-item', 'disabled' => empty($next)]) aria-disabled="{{ empty($next) }}">
				@empty($next)
					<span class="page-link">{!! __('pagination.next') !!}</span>
				@else
					<a class="page-link" rel="next"
						onclick="navigate('{{ request('query') }}',{{ (request('offset') ?? 0) + 20 }})">{!! __('pagination.next') !!}</a>
				@endempty
				</li>
			</ul>
		</nav>
	</div>
@else
<x-no-results source="deezer" />
@endif
