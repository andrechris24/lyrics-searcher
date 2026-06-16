@extends('layout')
@section('title', 'YouTube Search')
@section('subpage-title', 'YouTube Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="youtube" />
	</div>
@endsection
