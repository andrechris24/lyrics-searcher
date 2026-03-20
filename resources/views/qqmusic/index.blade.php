@extends('layout')
@section('title', 'QQ Music Lyrics Searcher')
@section('content')
	<div class="px-5 mx-5 py-5 my-5 text-center">
		<h3>QQ Music Lyrics Searcher</h3>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-basic provider="qqmusic" />
	</div>
@endsection
