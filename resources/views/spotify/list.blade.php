@if (count($data) > 0)
	<p class="text-center">Found {{ count($data) }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
		@php
		$album=$result['album'];
		$length = gmdate('i:s', $result['duration_ms']/1000);
		$artists=array();
		foreach ($result['artists'] as $artist) {
			$artists[]=$artist['name'];
		}
		$artistName=implode(', ', $artists);
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
								data-coreui-title="{{ $result['name'] }}"
								data-coreui-album="{{ $album['name'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-target="#modalMX">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button type="button" @class([
								'btn',
								'btn-info',
								'disabled' => empty($result['preview_url'])
							])
								aria-disabled="{{empty($result['preview_url'])}}"
								data-coreui-link="{{ $result['preview_url'] }}"
								data-coreui-artist="{{ $artistName }}"
								data-coreui-title="{{ $result['name'] }}"
								data-coreui-album="{{ $album['name'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-target="#modalPreviewSong">
								<i class="fa-solid fa-play"></i>
							</button>
							<a href="https://open.spotify.com/track/{{ $result['id'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($result['id'])
								]) aria-disabled="{{empty($result['id'])}}"
								data-coreui-toggle="tooltip" data-coreui-title="View on Spotify" target="_blank">
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
