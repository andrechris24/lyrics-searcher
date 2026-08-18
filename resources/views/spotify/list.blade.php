@if (count($data) > 0)
	<p class="text-center">Found {{ count($data) }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			<div class="col">
				<div class="card">
					<img src="{{ $result['album']['images'][0]['url'] }}" class="card-img-top"
						alt="{{ $result['album']['name'] }}">
					<div class="card-header">
						{{ $result['album']['name'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['name'] . ($result['explicit'] === true ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['artists'][0]['name'] }}</p>
						<small class="card-text text-muted">{{ gmdate('i:s',$result['duration_ms']/1000)  }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button class="btn btn-primary" data-coreui-id="{{ $result['id'] }}"
								data-coreui-artist="{{ $result['artists'][0]['name'] }}"
								data-coreui-title="{{ $result['name'] }}"
								data-coreui-album="{{ $result['album']['name'] }}"
								data-coreui-duration="{{ gmdate('i:s',$result['duration_ms']/1000) }}" data-coreui-toggle="modal"
								data-coreui-target="#modalMX">
								<i class="fa-solid fa-eye"></i>
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
