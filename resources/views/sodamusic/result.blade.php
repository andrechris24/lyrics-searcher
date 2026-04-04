@extends('layout')
@section('title', 'Soda Music Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Soda Music Search Results</h3>
		<x-basic provider="sodamusic" />
	</div>
	@include('sodamusic.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/sodamusic.js') }}"></script>
@endsection
