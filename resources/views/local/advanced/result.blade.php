@extends('layout')
@section('title', 'Advanced Local Search Results')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Advanced Local Search Results</h3>
		<x-advanced provider="local" require="0" />
	</div>
	@include('local.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
