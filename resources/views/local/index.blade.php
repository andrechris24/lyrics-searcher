@extends('layout')
@section('title', 'Local Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Local Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-two-form provider="local" type="basic" />
	</div>
	<x-upload-lyric />
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
