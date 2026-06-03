@extends('layout')
@section('title', 'Advanced Local Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Advanced Local Search</h3>
		@if (Session::has('error'))
			<x-error />
		@endif
		<x-advanced provider="local" require="0" />
	</div>
	<x-upload-lyric />
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/local.js') }}"></script>
@endsection
