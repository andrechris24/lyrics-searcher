@extends('layout')
@section('title', 'Musixmatch Lyrics Advanced Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Musixmatch Lyrics Advanced Search Results</h3>
		<x-limitation />
		<x-advanced provider="musixmatch" require="0" />
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
