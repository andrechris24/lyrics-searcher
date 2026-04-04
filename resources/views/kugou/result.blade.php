@extends('layout')
@section('title', 'Kugou Music Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Kugou Music Search Results</h3>
		<x-basic provider="kugou" />
	</div>
	@include('kugou.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/kugou.js') }}"></script>
@endsection
