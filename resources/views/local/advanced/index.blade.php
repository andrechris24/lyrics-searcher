@extends('layout')
@section('title', 'Advanced Local Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Advanced Local Search</h3>
		@if (Session::has('error'))
			<x-error />
		@endif
		<x-advanced provider="local" require="1" />
	</div>
@endsection
