@extends('layout')
@section('title', 'LRCLib Lyrics Searcher')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>LRCLib Lyrics Searcher</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-basic provider="lrclib" />
	</div>
@endsection
