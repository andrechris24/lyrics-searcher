@extends('layout')
@section('title', 'Spotify Search Results for ' . request('query'))
@section('subpage-title','Spotify Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="spotify" />
	</div>
	@include('spotify.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/spotify.js') }}"></script>
@endsection
