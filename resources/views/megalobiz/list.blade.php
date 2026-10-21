@empty($results)
	<x-no-results source="megalobiz" />
@else
	<p class="text-center">Found {{ $count }} result(s)</p>
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@foreach ($results as $result)
			@php($length = gmdate('i:s', $result['duration']))
			<a class="list-group-item list-group-item-action" data-coreui-toggle="modal"
				data-coreui-duration="{{ $length }}" data-coreui-title="{{ $result['title'] }}"
				data-coreui-artist="{{ $result['artist'] }}" data-coreui-id="{{ $result['id'] }}"
				data-coreui-type="{{$result['sync']}}"
				href="#modalLyrics">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['title'] }}</h5>
					<small>{{ $length }} | {{ $result['sync'] }}</small>
				</div>
				<p class="mb-1">{{ $result['artist'] }}</p>
				<small>Match: {{ $result['score'] * 100 }}%</small>
			</a>
		@endforeach
	</div>
	@endif
