<x-no-script />
@if (count($data) > 0)
	<p class="text-center">Found {{ count($data) }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($data as $result)
			<div class="col">
				<div class="card">
					<img src="{{ $result['thumbnail'] }}" class="card-img-top"
					alt="{{ $result['title'] }}">
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['title'] . ($result['isExplicit'] ? ' [E]' : '') }}
						</h5>
						<p class="card-text">{{ $result['author'] }}</p>
						<small class="card-text text-muted">{{ $result['duration'] }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button class="btn btn-primary download-btn" data-id="{{ $result['videoId'] }}"
								data-artist="{{ $result['author'] }}" data-title="{{ $result['title'] }}"
								data-duration="{{ $result['duration'] }}" data-bs-toggle="tooltip"
								data-bs-title="Download">
								<i class="fa-solid fa-download"></i>
							</button>
							<a href="https://www.youtube.com/watch?v={{ $result['videoId'] }}"
								@class([
									'btn',
									'btn-success',
									'disabled' => empty($result['videoId']),
								])
								@empty($result['videoId']) aria-disabled="true" @endempty
								data-bs-toggle="tooltip" data-bs-title="View on YouTube" target="_blank">
								<i class="fa-brands fa-youtube"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<x-no-results source="youtube" />
@endif
