<div class="row mb-3">
	<div class="col-12 col-md-4 mb-md-0 mb-2">
		<img src="{{ $thumbnail ?? $info['image'] }}" class="img-fluid" id="youtube-thumbnail"
			alt="YouTube Thumbnail">
	</div>
	<div class="col-12 col-md-8">
		{{-- @if (request()->routeIs('dl.youtube.video'))
			<div class="alert alert-danger" role="alert">
				Please do not use this tool to download copyrighted content without permission.
				Downloading copyrighted material may be illegal and can result in legal consequences.
				Also, do not alter metadata or remove watermarks from downloaded content, as this may
				violate copyright laws and ethical standards.
			</div>
		@endif --}}
		<div class="row mb-2">
			<div class="col-12">
				<b>{{ $title ?? $info['title'] }}</b>
			</div>
			@if (!empty($duration))
				<div class="col-12">
					{{ gmdate('i:s', $duration) }}
				</div>
			@endif
		</div>
		@if (request()->routeIs('dl.youtube.alt'))
			<a href="{{ $download }}" class="btn btn-primary" id="youtube-dl-alt"
				target="_blank">Download Link</a>
		@else
			<div class="btn btn-group" role="group" aria-label="Download Links">
				<a href="{{ $original_url }}" class="btn btn-primary" id="youtube-url1"
					target="_blank">Link 1</a>
				<a href="{{ $url }}" class="btn btn-secondary" id="youtube-url2"
					target="_blank">Link 2</a>
			</div>
		@endif
	</div>
</div>
