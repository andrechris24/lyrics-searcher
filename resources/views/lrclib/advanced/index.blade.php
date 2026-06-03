@extends('layout')
@section('title', 'LRCLib Advanced Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>LRCLib Advanced Search</h3>
		@if (Session::has('error'))
			<x-error />
		@endif
		<x-advanced provider="lrclib" require="1" />
	</div>
@endsection
