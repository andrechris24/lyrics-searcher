<x-no-script />
@empty($data)
	<x-no-results source="local" />
@else
	<p class="text-center">Page {{$data->currentPage()}} out of {{$data->total()}} result(s), showing {{$data->perPage()}} per page. Click on a list to download.</p>
	<div class="list-group mx-5 mb-5 pb-5">
		@foreach ($data as $result)
			@php($length = gmdate('i:s', $result['duration']))
			<a class="list-group-item list-group-item-action"
				data-album="{{ $result['album'] }}" data-duration="{{ $length }}"
				data-title="{{ $result['title'] }}" data-artist="{{ $result['artist'] }}"
				data-content="{{ $result['content'] }}" data-id="{{$result['id']}}"
				data-user="{{ $result['by'] }}" data-offset="{{$result['offset']}}" href="#">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['title'] }}</h5>
					<small>{{ $length }} | by {{$result['by']}}</small>
				</div>
				<p class="mb-1">{{ $result['artist'] }}</p>
				<small>{{ $result['album'] }}</small>
			</a>
		@endforeach
	</div>
	{{$data->links()}}
@endif
