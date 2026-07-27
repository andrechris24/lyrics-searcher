@extends('layout')
@section('title', 'Musixmatch Charts')
@section('subpage-title', 'Musixmatch Charts')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 text-center">
		<form action="#" class="row g-3 mb-3" id="chart-form">
			<div class="row g-2">
				<div class="col-12 col-md-7">
					<div class="form-floating">
						<select class="form-select" id="chart-type" name="chart" required>
							<option value="">Select</option>
							<option value="top">Top Songs</option>
							<option value="hot">Most Viewed</option>
							<option value="mxmweekly">Weekly</option>
							<option value="mxmweekly_new">New Releases</option>
						</select>
						<label for="search-type">Type</label>
					</div>
				</div>
				<div class="col-12 col-md-5">
					<div class="form-check">
						<input type="checkbox" name="worldwide" class="btn-check" id="btn-check"
							autocomplete="off">
						<label class="btn btn-outline-info" for="btn-check">Show Worldwide Charts</label>
					</div>
					<small class="form-text">Not working with top songs</small>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Show</button>
			<small class="form-text">
				<a href="{{ route('musixmatch.index') }}">Go to search</a>
				<a href="{{ route('musixmatch.advanced') }}">Go to Advanced search</a>
			</small>
		</form>
	</div>
	@include('musixmatch.skeleton')
	<div id="mx-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ajax/mx.js') }}"></script>
@endsection
