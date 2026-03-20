@extends('layout')
@section('title', 'LRCLib Advanced Search Results')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>LRCLib Advanced Search Results</h3>
		<x-advanced provider="lrclib" require="1" />
	</div>
	@include('lrclib.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/lrclib.js') }}"></script>
@endsection
