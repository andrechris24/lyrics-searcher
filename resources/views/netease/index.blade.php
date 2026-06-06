@extends('layout')
@section('title', 'NetEase Music Search')
@section('subpage-title','NetEase Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="netease" />
	</div>
@endsection
