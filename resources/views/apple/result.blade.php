@extends('layout')
@section('title', 'Apple Music Search Results for ' . request('query'))
@section('subpage-title','Apple Music Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="apple" />
	</div>
	@include('apple.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/apple.js') }}"></script>
@endsection
