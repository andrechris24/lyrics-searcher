@extends('layout')
@section('title', 'lrc.red Lyrics Search')
@section('subpage-title', 'lrc.red Lyrics Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="lrcred" />
	</div>
	@include('lrcred.skeleton')
	<div id="lrcred-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/lrcred.js') }}"></script>
@endsection
