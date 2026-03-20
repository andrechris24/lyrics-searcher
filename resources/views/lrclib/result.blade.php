@extends('layout')
@section('title', 'LRCLib Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>LRCLib Search Results</h3>
		<x-basic provider="lrclib" />
	</div>
	@include('lrclib.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/lrclib.js') }}"></script>
@endsection
