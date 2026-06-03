@extends('layout')
@section('title', 'Local Search Results for ' . request('query'))
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Local Search Results</h3>
		<x-two-form provider="local" type="basic" />
	</div>
	<x-upload-lyric />
	@include('local.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
