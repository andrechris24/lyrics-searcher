@extends('layout')
@section('title', 'Musixmatch ' . $typeName)
@section('subpage-title', 'Musixmatch ' . $typeName)
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 text-center">
		@if($position===false))
		<div class="alert alert-warning mb-2">
			Unable to get your location. Showing {{request('type')==='top'?'United States':'Global'}} charts instead.
		</div>
		@else
		<div class="alert alert-info mb-2">
			Detected country: {{$position->countryName}}. This location is based on your IP.
		</div>
		@endif
		<a href="{{ route('musixmatch.index') }}">Go to search</a>
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
