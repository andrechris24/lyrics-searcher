@extends('layout')
@section('title', 'Musixmatch Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Musixmatch Search Results</h3>
		<x-limitation />
		<x-basic provider="musixmatch" />
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
