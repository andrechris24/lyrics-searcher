@extends('layout')
@section('title', 'Soda Music Search Results for ' . request('query'))
@section('subpage-title','Soda Music Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="sodamusic" />
	</div>
	@include('sodamusic.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/sodamusic.js') }}"></script>
@endsection
