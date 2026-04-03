@extends('layout')
@section('title', 'Kugou Lyrics download')
@section('content')
	<h3 class="text-center py-5 mt-5">Kugou Lyrics download</h3>
	@if (count($data) > 0)
		<div class="list-group mx-5 px-5 mb-5 pb-5">
			@foreach ($data as $result)
				<a class="list-group-item list-group-item-action download-btn" href="#"
					data-id="{{ $result['id'] }}" data-key="{{ $result['accesskey'] }}">
					<div class="d-flex w-100 justify-content-between">
						<h5 class="mb-1">{{ $result['song'] }}</h5>
					</div>
					<p class="mb-1">{{ $result['singer'] }}</p>
					<small>{{ gmdate('i:s', $result['duration'] / 1000) }}</small>
				</a>
			@endforeach
		</div>
	@else
		<div class="alert alert-warning" role="alert">
			There are no lyrics available for selected song.
		</div>
	@endif
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/kugou.js') }}"></script>
@endsection
