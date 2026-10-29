@empty($results)
	<x-no-results source="lrcred" />
@else
	<p class="text-center">Found {{ $total }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($results as $track)
			@php
				$length = gmdate('i:s', $track['duration']);
				switch ($track['timing_type']) {
					case 'word':
						$lyricType = 'Syllable';
						$color = 'text-success';
						break;
					case 'line':
						$lyricType = 'Synced';
						$color = 'text-primary';
						break;
					case 'none':
						$lyricType = 'Plain';
						$color = 'text-info';
						break;
					default:
						$lyricType = $track['timing_type'];
						$color = 'text-warning';
						break;
				}
				$trackUrl = 'https://lrc.red/s/' . $track['isrc'];
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $trackUrl }}.webp" class="card-img-top" alt="{{ $track['track_name'] }}">
					<div class="card-header">
						{{ $track['album_name'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">{{ $track['track_name'] }}</h5>
						<p class="card-text">{{ $track['artist_name'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<span class="{{ $color }}"><b>{{ $lyricType }}</b></span>
						</li>
					</ul>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<div class="btn-group" role="group">
								<button type="button" class="btn btn-primary dropdown-toggle"
									data-coreui-toggle="dropdown" aria-expanded="false">
									<i class="fa-solid fa-download" data-coreui-toggle="tooltip"
										data-coreui-title="Download lyric"></i>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a class="dropdown-item" href="{{ $trackUrl }}.lrc" target="_blank">
											LRC
										</a>
									</li>
									<li>
										<a class="dropdown-item" href="{{ $trackUrl }}.ttml" target="_blank">
											TTML
										</a>
									</li>
									<li>
										<a class="dropdown-item" href="{{ $trackUrl }}.lyricsfile.yaml"
											target="_blank">
											Lyricsfile
										</a>
									</li>
								</ul>
							</div>
							<a href="{{ $trackUrl }}" class="btn btn-info" target="_blank"
								data-coreui-toggle="tooltip" data-coreui-title="View on lrc.red">
								<i class="fa-solid fa-arrow-up-right-from-square"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@endif
