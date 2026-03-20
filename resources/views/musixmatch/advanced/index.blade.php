@extends('layout')
@section('title', 'Musixmatch Advanced Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Musixmatch Advanced Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-advanced provider="musixmatch" require="0" />
	</div>
@endsection
