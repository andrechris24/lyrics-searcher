@extends('layout')
@section('title', 'Latest uploaded lyrics on local server')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Latest uploaded lyrics</h3>
		<a href="{{ route('local.index') }}">Go to search</a>
		@auth(backpack_guard_name())
			<a href="#modalUploadLyric" data-bs-toggle="modal">Upload lyrics</a>
		@endauth
	</div>
	<x-upload-lyric />
	@include('local.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
