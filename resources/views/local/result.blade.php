@extends('layout')
@section('title', 'Local Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Local Search Results</h3>
		<x-two-form provider="local" type="basic" />
	</div>
	@include('local.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
