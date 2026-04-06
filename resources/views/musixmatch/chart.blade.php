@extends('layout')
@section('title', 'Musixmatch ' . request('type') . ' charts')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Musixmatch {{ request('type') }} charts</h3>
		<x-limitation />
	</div>
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
