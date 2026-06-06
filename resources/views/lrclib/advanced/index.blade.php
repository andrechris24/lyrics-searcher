@extends('layout')
@section('title', 'LRCLib Advanced Search')
@section('subpage-title','LRCLib Advanced Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-advanced provider="lrclib" require="1" />
	</div>
@endsection
