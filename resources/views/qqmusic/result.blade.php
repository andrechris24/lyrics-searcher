@extends('layout')
@section('title', 'QQ Music Search Results for ' . request('query'))
@section('subpage-title','QQ Music Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="qqmusic" />
	</div>
	@include('qqmusic.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/qqmusic.js') }}"></script>
@endsection
