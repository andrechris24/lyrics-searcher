@if (count($data) > 0)
	<p class="text-center">Found {{ count($data) }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			<div class="col">
				<div class="card">
					<img src="{{ $result['albumCover'] }}" class="card-img-top"
						alt="{{ $result['albumName'] }}">
					<div class="card-header">
						{{ $result['albumName'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['name'] . ($result['contentRating'] === 'EXPLICIT' ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artistName'] }}</p>
						<small class="card-text text-muted">{{ $result['duration'] }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button class="btn btn-primary" data-coreui-id="{{ $result['trackId'] }}"
								data-coreui-artist="{{ $result['artistName'] }}"
								data-coreui-title="{{ $result['name'] }}"
								data-coreui-album="{{ $result['albumName'] }}"
								data-coreui-duration="{{ $result['duration'] }}" data-coreui-toggle="modal"
								data-coreui-target="#modalMX">
								<i class="fa-solid fa-eye"></i>
							</button>
							<a href="https://open.spotify.com/track/{{ $result['trackId'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($result['trackId'])
								]) aria-disabled="{{empty($result['trackId'])}}"
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
