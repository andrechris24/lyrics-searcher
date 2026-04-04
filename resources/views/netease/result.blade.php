@extends('layout')
@section('title', 'NetEase Music Search Results for ' . request('query'))
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>NetEase Music Search Results</h3>
		<x-basic provider="netease" />
	</div>
	@include('netease.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/netease.js') }}"></script>
@endsection
