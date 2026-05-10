@extends('layout')
@section('title', 'Kugou Music Advanced Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Kugou Music Advanced Search Results</h3>
		<x-advanced-alt provider="kugou" />
	</div>
	@if (count($data) > 0)
		<div class="list-group mx-5 px-5 mb-5 pb-5">
			@foreach ($data as $result)
				<a class="list-group-item list-group-item-action" href="javascript:void(0)"
					onclick="fileName='{{ $result['singer'] . ' - ' . $result['song'] }}';dlLRC({{ $result['id'] }},'{{ $result['accesskey'] }}');">
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
			There are no lyrics available for your search query.
			<a
				href="{{ route('kugou.search', ['query' => request('artist') . ' ' . request('title')]) }}">
				Click here</a> for basic search.
		</div>
	@endif
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/kugou.js') }}"></script>
@endsection
