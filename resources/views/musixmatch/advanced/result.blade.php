@extends('layout')
@section('title', 'Musixmatch Lyrics Advanced Search Results')
@section('subpage-title','Musixmatch Lyrics Advanced Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-musixmatch.advanced />
	</div>
	<x-limitation />
	@include('musixmatch.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/musixmatch.js') }}"></script>
@endsection
