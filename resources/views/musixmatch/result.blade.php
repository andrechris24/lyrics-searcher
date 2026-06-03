@extends('layout')
@section('title', 'Musixmatch Lyrics Search Results for ' . request('query'))
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Musixmatch Lyrics Search Results</h3>
		<x-limitation />
		<x-basic provider="musixmatch" />
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
