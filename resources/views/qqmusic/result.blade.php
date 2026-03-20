@extends('layout')
@section('title', 'QQ Music Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>QQ Music Search Results</h3>
		<x-basic provider="qqmusic" />
	</div>
	@include('qqmusic.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/qqmusic.js') }}"></script>
@endsection
