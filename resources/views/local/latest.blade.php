@extends('layout')
@section('title', 'Latest uploaded lyrics on local server')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Latest uploaded lyrics</h3>
		<a href="{{ route('local.index') }}">Go to search</a>
	</div>
	<x-upload-lyric />
	@include('local.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
