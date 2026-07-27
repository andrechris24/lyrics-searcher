@extends('layout')
@section('title', 'Soda Music Search')
@section('subpage-title', 'Soda Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="sodamusic" />
	</div>
	@include('sodamusic.skeleton')
	<div id="sodamusic-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/soda.js') }}"></script>
@endsection
