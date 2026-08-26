@if (count($data) > 0)
	<p class="text-center">Found {{ count($data) }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			<div class="col">
				<div class="card">
					<img src="{{ $result['cover'] }}" class="card-img-top" alt="{{ $result['title'] }}">
					<div class="card-body">
						<h5 class="card-title">{{ $result['title']}}</h5>
						<p class="card-text">{{ $result['artist'] }}</p>
						<small class="card-text text-muted">{{ implode(',',$result['tags'] ) }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button class="btn btn-primary" data-coreui-id="{{ $result['track_id'] }}"
								data-coreui-artist="{{ $result['artist'] }}" data-coreui-track="{{ $result['title'] }}"
								data-coreui-toggle="modal" data-coreui-target="#modalLyrics"
								data-coreui-toggle2="tooltip" data-coreui-title="Show & Download lyric">
								<i class="fa-solid fa-eye"></i>
							</button>
							<button class="btn btn-secondary download-btn" data-href="{{ $result['deeplink'] }}" @disabled(empty($result['deeplink']))
								data-artist="{{ $result['artist'] }}" data-title="{{ $result['title'] }}"
								data-coreui-toggle="tooltip" data-coreui-title="Download song">
								<i class="fa-solid fa-download"></i>
							</button>
							<a href="{{ $result['deeplink'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($result['deeplink'])
								]) aria-disabled="{{ empty($result['deeplink']) }}"
								data-coreui-toggle="tooltip" data-coreui-title="Go to Amazon Music" target="_blank">
								<i class="fa-brands fa-amazon"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<x-no-results source="amazon" />
@endif
