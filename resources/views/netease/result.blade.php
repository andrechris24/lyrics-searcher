@extends('layout')
@section('title', 'NetEase Music Search Results for ' . request('query'))
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>NetEase Music Search Results</h3>
		<x-basic provider="netease" />
	</div>
	@include('netease.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/netease.js') }}"></script>
@endsection
