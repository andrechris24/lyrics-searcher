@if ($resultCount > 0)
	<p class="text-center">Found {{ $resultCount }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($results as $result)
			@php
				$length = gmdate(
				    'i:s',
				    round($result['trackTimeMillis'] / 1000, 0, PHP_ROUND_HALF_UP)
				);
				$art = !empty($result['artworkUrl100'])
				    ? $result['artworkUrl100']
				    : (!empty($result['artworkUrl60'])
				        ? $result['artworkUrl60']
				        : (!empty($result['artworkUrl30'])
				            ? $result['artworkUrl30']
				            : 'https://placehold.co/500?text=' .
				                urlencode($result['album']['name'])));
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $result['trackName'] }}">
					<div class="card-header">
						{{ $result['collectionName'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['trackName'] . ($result['trackExplicitness'] === 'Explicit' ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artistName'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-primary" data-coreui-toggle="modal"
								data-coreui-target="#modalLyrics" data-coreui-id="{{ $result['trackId'] }}"
								data-coreui-artist="{{ $result['artistName'] }}"
								data-coreui-title="{{ $result['trackName'] }}"
								data-coreui-album="{{ $result['collectionName'] }}"
								data-coreui-duration="{{ $length }}">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button type="button" @class([
								'btn',
								'btn-info',
								'disabled' => empty($result['previewUrl'])
							])
								@if (empty($result['previewUrl'])) aria-disabled="true" @endif
								data-coreui-link="{{ $result['previewUrl'] }}"
								data-coreui-artist="{{ $result['artistName'] }}"
								data-coreui-title="{{ $result['trackName'] }}"
								data-coreui-album="{{ $result['collectionName'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-target="#modalPreviewSong">
								<i class="fa-solid fa-play"></i>
							</button>
							<a href="{{ $result['trackViewUrl'] }}" @class([
								'btn',
								'btn-success',
								'disabled' => empty($result['trackViewUrl'])
							])
								@empty($result['trackViewUrl']) aria-disabled="true" @endempty
								data-coreui-toggle="tooltip" data-coreui-title="View on Apple Music"
								target="_blank">
								<i class="fa-brands fa-itunes-note"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<x-no-results source="apple" />
@endif
