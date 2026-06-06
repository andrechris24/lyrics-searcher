@extends('layout')
@section('title', 'Musixmatch ' . request('type') . ' charts')
@section('subpage-title','Musixmatch ' . request('type') . ' charts')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 text-center">
		<a href="{{ route('musixmatch.index') }}">Go to search</a>
	</div>
		<x-limitation />
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
