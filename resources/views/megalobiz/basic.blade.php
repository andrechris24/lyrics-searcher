@extends('layout')
@section('title', 'Megalobiz Search')
@section('subpage-title', 'Megalobiz Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<x-basic provider="megalobiz" />
	</div>
	@include('megalobiz.modal')
	@include('megalobiz.skeleton')
	<div id="megalobiz-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/megalobiz.js') }}"></script>
@endsection
