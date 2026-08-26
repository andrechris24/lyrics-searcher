@extends('layout')
@section('title', 'Musixmatch Lyrics Search')
@section('subpage-title', 'Musixmatch Lyrics Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<form action="#" class="row g-3 mb-3" id="mx-basic-form">
			<div class="row g-2">
				<div class="col-md-8">
					<div class="input-group input-group-lg mb-3">
						<span class="input-group-text">
							<i class="fa-solid fa-magnifying-glass"></i>
						</span>
						<div class="form-floating">
							<input type="search" name="query" placeholder="Enter search query here..."
								class="form-control form-control-lg"id="musixmatch-query" required autofocus>
							<label for="musixmatch-query">Search query</label>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<select class="form-select" id="search-type" name="type" required>
							<option value="">Select</option>
							<option value="all">All</option>
							<option value="track">Song title</option>
							<option value="artist">Artist</option>
							<option value="lyrics">Lyric</option>
							<option value="track_artist">Title and Artist</option>
							<option value="writer">Song writer</option>
						</select>
						<label for="search-type">Type</label>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Search</button>
			<small class="form-text">
				<a href="{{ route('musixmatch.advanced') }}">Advanced search</a>
				<a href="{{ route('musixmatch.chart') }}">Show Charts</a>
			</small>
		</form>
	</div>
	@include('musixmatch.skeleton')
	<div id="mx-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/mx.js') }}"></script>
@endsection
