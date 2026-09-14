@if ($resultCount ?? count($results) > 0)
	<p class="text-center">Found {{ $resultCount ?? count($results) }} result(s)</p>
	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3">
		@foreach ($results as $result)
			@php
				$length = gmdate(
					'i:s',
					round(($result['trackTimeMillis']??$result['durationInMillis']) / 1000, 0, PHP_ROUND_HALF_UP)
				);
				$art = $result['artworkUrl100']?? $result['artworkUrl60'] ?? $result['artworkUrl30']
						?? $result['artwork']['url'] ?? 'https://placehold.co/500?text=' .urlencode($result['albumName']??$result['album']['name']);
			@endphp
			<div class="col">
				<div class="card">
					<img src="{{ $art }}" class="card-img-top" alt="{{ $result['trackName']??$result['name'] }}">
					<div class="card-header">
						{{ $result['collectionName']??$result['albumName'] }}
					</div>
					<div class="card-body">
						<h5 class="card-title">
							{{ $result['trackName']??$result['name'] }}
						</h5>
						<p class="card-text">{{ $result['artistName'] }}</p>
						<small class="card-text text-muted">{{ $length }}</small>
					</div>
					<div class="card-footer">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-primary" data-coreui-toggle="modal"
								data-coreui-target="#modalLyrics" data-coreui-id="{{ $result['trackId']??$result['playParams']['id'] }}"
								data-coreui-artist="{{ $result['artistName'] }}"
								data-coreui-track="{{ $result['trackName']??$result['name'] }}"
								data-coreui-album="{{ $result['collectionName']??$result['albumName'] }}"
								data-coreui-duration="{{ $length }}">
								<i class="fa-solid fa-eye" data-coreui-toggle="tooltip"
								data-coreui-title="Show & download lyric"></i>
							</button>
							<button type="button" class="btn btn-info" @disabled(!array_key_exists('previewUrl', $result) && !array_key_exists('previews',$result))
								data-coreui-link="{{ $result['previewUrl']??$result['previews'][0]['url'] ?? '#' }}"
								data-coreui-artist="{{ $result['artistName'] }}"
								data-coreui-track="{{ $result['trackName']??$result['name'] }}"
								data-coreui-album="{{ $result['collectionName']??$result['albumName'] }}"
								data-coreui-duration="{{ $length }}" data-coreui-toggle="modal"
								data-coreui-target="#modalPreviewSong">
								<i class="fa-solid fa-play" data-coreui-toggle="tooltip"
								data-coreui-title="Preview song"></i>
							</button>
							<button type="button" class="btn btn-secondary download-btn"
								@disabled((empty($result['trackViewUrl']) && empty($result['url'])) || empty(env('PAXSENIX_TOKEN'))) data-href="{{ $result['trackViewUrl']??$result['url'] }}"
								data-artist="{{ $result['artistName'] }}"
								data-title="{{ $result['trackName']??$result['name'] }}" data-coreui-toggle="tooltip"
								data-coreui-title="Download song (Buggy server)">
								<i class="fa-solid fa-download"></i>
							</button>
							<a href="{{ $result['trackViewUrl']??$result['url']??'#' }}" @class([
								'btn',
								'btn-success',
								'disabled' => empty($result['trackViewUrl']) && empty($result['url'])
							])
								aria-disabled="{{ empty($result['trackViewUrl'])&& empty($result['url']) }}" target="_blank"
								data-coreui-toggle="tooltip" data-coreui-title="Go to Apple Music">
								<i class="fa-brands fa-apple"></i>
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
