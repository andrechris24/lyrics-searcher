@extends('layout')
@section('title', 'Musixmatch Lyrics Advanced Search')
@section('subpage-title', 'Musixmatch Lyrics Advanced Search')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<form class="row g-3 mb-3" action="#" id="mx-advanced-form">
			<div class="col-sm-6">
				<div class="input-group input-group-lg">
					<span class="input-group-text">
						<i class="fa-solid fa-music"></i>
					</span>
					<div class="form-floating">
						<input type="text" class="form-control" id="track-name" placeholder="Song title"
							name="title" value="{{ request('title') ?? old('title') }}" autofocus>
						<label for="track-name" class="form-label">Song Title</label>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="input-group input-group-lg">
					<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
					<div class="form-floating">
						<input type="text" class="form-control" id="artist-name" placeholder="Artist"
							name="artist" value="{{ request('artist') ?? old('artist') }}">
						<label for="artist-name" class="form-label">Artist</label>
					</div>
				</div>
			</div>
			<div class="col-12">
				<div class="input-group input-group-lg">
					<span class="input-group-text">
						<i class="fa-solid fa-compact-disc"></i>
					</span>
					<div class="form-floating">
						<input type="text" class="form-control" id="lyric-keyword"
							placeholder="Any word in lyric" name="lyrics"
							value="{{ request('lyrics') ?? old('lyrics') }}">
						<label for="lyric-keyword" class="form-label">Lyric Keyword</label>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Search</button>
			<small class="form-text">
				<a href="{{ route('musixmatch.index') }}">Basic search</a>
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
