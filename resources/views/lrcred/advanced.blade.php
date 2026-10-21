@extends('layout')
@section('title', 'lrc.red Advanced Lyrics Search')
@section('subpage-title', 'lrc.red Advanced Lyrics Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-advanced provider="lrcred" require="2" />
	</div>
	@include('lrcred.skeleton')
	<div id="lrcred-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/lrcred.js') }}"></script>
@endsection
