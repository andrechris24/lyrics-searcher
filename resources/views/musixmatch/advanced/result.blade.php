@extends('layout')
@section('title', 'Musixmatch Advanced Search Results')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Musixmatch Advanced Search Results</h3>
		<x-limitation />
		<x-advanced provider="musixmatch" require="0" />
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
