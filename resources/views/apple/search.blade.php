@extends('layout')
@section('title', 'Apple Music Search')
@section('subpage-title', 'Apple Music Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<form action="#" class="row g-3 mb-3" id="apple-form">
			<div class="row g-2">
				<div class="col-md-8">
					<div class="input-group input-group-lg mb-3">
						<span class="input-group-text">
							<i class="fa-solid fa-magnifying-glass"></i>
						</span>
						<div class="form-floating">
							<input type="search" name="query" placeholder="Enter search query here..."
								class="form-control form-control-lg"id="apple-query" required autofocus>
							<label for="musixmatch-query">Search query</label>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<select class="form-select" id="api-version" name="api" required>
							<option value="">Select</option>
							<option value="itunes" @selected(empty(env('PAXSENIX_TOKEN')))>
								iTunes
							</option>
							<option value="paxsenix" @disabled(empty(env('PAXSENIX_TOKEN')))>
								Paxsenix
							</option>
						</select>
						<label for="search-type">Source</label>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Search</button>
		</form>
	</div>
	@include('apple.skeleton')
	@include('apple.modal')
	<div id="apple-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/apple.js') }}"></script>
@endsection
