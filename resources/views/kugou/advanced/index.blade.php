@extends('layout')
@section('title', 'Kugou Music Advanced Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Kugou Music Advanced Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-alternative provider="kugou" />
	</div>
@endsection
