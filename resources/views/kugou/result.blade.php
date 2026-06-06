@extends('layout')
@section('title', 'Kugou Music Search Results for ' . request('query'))
@section('subpage-title','Kugou Music Search Results')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="kugou" />
	</div>
	@include('kugou.list')
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/kugou.js') }}"></script>
@endsection
