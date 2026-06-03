@extends('layout')
@section('title', 'Soda Music Search Results for ' . request('query'))
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Soda Music Search Results</h3>
		<x-basic provider="sodamusic" />
	</div>
	@include('sodamusic.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/sodamusic.js') }}"></script>
@endsection
