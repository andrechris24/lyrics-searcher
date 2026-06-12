@extends('layout')
@section('title', 'QQ Music Search')
@section('subpage-title', 'QQ Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-two-form provider="qqmusic" type="basic" />
	</div>
@endsection
