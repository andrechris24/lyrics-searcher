@extends('layout')
@section('title', 'Deezer Search')
@section('subpage-title', 'Deezer Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="deezer" />
	</div>
@endsection
