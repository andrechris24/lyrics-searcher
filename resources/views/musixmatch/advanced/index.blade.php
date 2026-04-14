@extends('layout')
@section('title', 'Musixmatch Lyrics Advanced Search')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>Musixmatch Lyrics Advanced Search</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-advanced provider="musixmatch" require="0" />
	</div>
@endsection
