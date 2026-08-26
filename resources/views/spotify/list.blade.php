@if ($data['total'] > 0)
	@if ($data['total'] > $data['limit'])
		<div class="callout callout-warning">
			<div class="text-center">Due to API limitation, only first {{ $data['limit'] }} are
				returned from {{ $data['total'] }} results.</div>
		</div>
	@else
		<p class="text-center">Found {{ $data['total'] }} result(s)</p>
	@endif
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data['items'] as $result)
			@php
				$album = $result['album'];
				$length = gmdate('i:s', $result['duration_ms'] / 1000);
				$artists = [];
				foreach ($result['artists'] as $artist) {
				    $artists[] = $artist['name'];
				}
				$artistName = implode(', ', $artists);
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $album['images'][0]['url'] }}" class="card-img-top"
						alt="{{ $album['name'] }}">
					<div class="card-header">
						{{ $album['name'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['name'] . ($result['explicit'] === true ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $artistName }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button class="btn btn-primary" data-coreui-id="{{ $result['id'] }}"
								data-coreui-artist="{{ $artistName }}"
								data-coreui-track="{{ $result['name'] }}"
								data-coreui-album="{{ $album['name'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-toggle2="tooltip" data-coreui-title="Show & download lyric"
								data-coreui-target="#modalMX">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button type="button" class="btn btn-info" @disabled(empty($result['preview_url']))
								data-coreui-link="{{ $result['preview_url'] }}"
								data-coreui-artist="{{ $artistName }}"
								data-coreui-track="{{ $result['name'] }}"
								data-coreui-album="{{ $album['name'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-toggle2="tooltip" data-coreui-title="Preview song"
								data-coreui-target="#modalPreviewSong">
								<i class="fa-solid fa-play"></i>
							</button>
							<button type="button" class="btn btn-secondary download-btn"
								@disabled(empty($result['id']) || empty(env('PAXSENIX_TOKEN')))
								data-href="{{ $result['external_urls']['spotify'] }}"
								data-artist="{{ $artistName }}" data-title="{{ $result['name'] }}"
								data-coreui-toggle="tooltip" data-coreui-title="Download song">
								<i class="fa-solid fa-download"></i>
							</button>
							<a href="{{ $result['external_urls']['spotify'] }}" @class(['btn', 'btn-success', 'disabled' => empty($result['id'])])
								aria-disabled="{{ empty($result['id']) }}" data-coreui-toggle="tooltip"
								data-coreui-title="Go to Spotify" target="_blank">
								<i class="fa-brands fa-spotify"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<x-no-results source="spotify" />
@endif
