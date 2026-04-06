<x-no-script />
@empty($data)
	<x-no-results source="qqmusic" />
@else
	<p class="text-center">Found {{ $data['count'] }} result(s)</p>
	<div class="list-group mx-5 px-5 mb-5 pb-5">
		@foreach ($data['itemlist'] as $result)
			<a class="list-group-item list-group-item-action" href="#"
				data-title="{{ $result['name'] }}" data-artist="{{ $result['singer'] }}"
				data-id="{{ $result['mid'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['name'] }}</h5>
				</div>
				<p class="mb-1">{{ $result['singer'] }}</p>
			</a>
		@endforeach
	</div>
@endempty
