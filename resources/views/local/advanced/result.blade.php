@extends('layout')
@section('title', 'Advanced Local Search Results')
@section('subpage-title','Advanced Local Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-advanced provider="local" require="0" />
	</div>
	<x-upload-lyric />
	@include('local.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
