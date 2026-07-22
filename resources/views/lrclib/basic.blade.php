@extends('layout')
@section('title', 'LRCLib Search')
@section('subpage-title', 'LRCLib Search')
@section('content')
	<x-no-script />
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="lrclib" />
	</div>
	<x-lrclib-modal text="Preview lyric" />
	@include('lrclib.skeleton')
	<div id="lrclib-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/lrclib.js') }}"></script>
@endsection
