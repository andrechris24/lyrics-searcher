@extends('layout')
@section('title', 'Apple Music Search')
@section('subpage-title', 'Apple Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="apple" />
	</div>
	@include('apple.skeleton')
	@include('apple.modal')
	<div id="apple-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/apple.js') }}"></script>
@endsection
