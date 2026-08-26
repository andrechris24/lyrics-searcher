@empty($songinfo)
	<x-no-results source="qqmusic" />
@else
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@if (!array_key_exists('@attributes', $songinfo))
			@foreach ($songinfo as $result)
				<a class="list-group-item list-group-item-action" href="#"
					data-title="{{ urldecode($result['name']) }}"
					data-artist="{{ urldecode($result['singername']) }}"
					data-id="{{ $result['@attributes']['id'] }}">
					<div class="d-flex w-100 justify-content-between">
						<h5 class="mb-1">{{ urldecode($result['name']) }}</h5>
					</div>
					<p class="mb-1">{{ urldecode($result['singername']) }}</p>
					<small>{{ empty($result['albumname']) ? '' : urldecode($result['albumname']) }}</small>
				</a>
			@endforeach
		@else
			<a class="list-group-item list-group-item-action" href="#"
				data-title="{{ urldecode($songinfo['name']) }}"
				data-artist="{{ urldecode($songinfo['singername']) }}"
				data-id="{{ $songinfo['@attributes']['id'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ urldecode($songinfo['name']) }}</h5>
				</div>
				<p class="mb-1">{{ urldecode($songinfo['singername']) }}</p>
				<small>{{ empty($result['albumname']) ? '' : urldecode($result['albumname']) }}</small>
			</a>
		@endif
	</div>
@endempty
