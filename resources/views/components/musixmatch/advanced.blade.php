<form class="row g-3 mb-3" action="{{ route('musixmatch.search.advanced') }}">
	<div class="col-sm-6">
		<div class="input-group input-group-lg">
			<span class="input-group-text">
				<i class="fa-solid fa-music"></i>
			</span>
			<div class="form-floating">
				<input type="text" class="form-control @error('title') is-invalid @enderror "
				id="track-name" placeholder="Song title" name="title"
				value="{{ request('title') ?? old('title') }}"
				@if (request()->routeIs('*.index')) autofocus @endif>
				<label for="track-name" class="form-label">Song Title</label>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
			<div class="form-floating">
				<input type="text" class="form-control @error('artist') is-invalid @enderror "
					id="artist-name" placeholder="Artist" name="artist"
					value="{{ request('artist') ?? old('artist') }}">
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
				<input type="text" class="form-control @error('lyrics') is-invalid @enderror "
				id="lyric-keyword" placeholder="Any word in lyric" name="lyrics"
				value="{{ request('lyrics') ?? old('lyrics') }}">
				<label for="lyric-keyword" class="form-label">Lyric Keyword</label>
			</div>
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		<a href="{{ route('musixmatch.index') }}">Basic search</a>
	</small>
</form>
