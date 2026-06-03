@extends('layout')
@section('title', 'QQ Music Search Results for ' . request('query'))
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>QQ Music Search Results</h3>
		<x-basic provider="qqmusic" />
	</div>
	@include('qqmusic.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/qqmusic.js') }}"></script>
@endsection
