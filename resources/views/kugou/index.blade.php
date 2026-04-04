@extends('layout')
@section('title', 'Kugou Music Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Kugou Music Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-basic provider="kugou" />
	</div>
@endsection
