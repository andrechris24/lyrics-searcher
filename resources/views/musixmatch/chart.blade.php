@extends('layout')
@section('title', 'Musixmatch ' . request('type') . ' charts')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Musixmatch {{ request('type') }} charts</h3>
		<a href="{{ route('musixmatch.index') }}">Go to search</a>
		<x-limitation />
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
