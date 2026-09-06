@extends('layout')
@section('title', 'Spotify Search')
@section('subpage-title', 'Spotify Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="spotify" />
	</div>
	@include('spotify.skeleton')
	@include('spotify.modal')
	<div id="spotify-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/spotify.js') }}"></script>
@endsection
