@extends('layout')
@section('title', 'Kugou Music Advanced Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 py-5 my-5 text-center">
		<h3>Kugou Music Advanced Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-advanced-alt provider="kugou" />
	</div>
@endsection
