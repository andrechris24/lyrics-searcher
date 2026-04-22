@extends('layout')
@section('title', 'Local Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Local Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-basic provider="local" />
	</div>
@endsection
