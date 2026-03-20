<form class="row g-3 mb-3" action="{{ route($provider . '.search.advanced') }}">
	<div class="col-12">
		<label for="track-name" class="form-label">
			Song Title @if($require===1) <span class="text-danger"><b>*</b></span> @endif
		</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
			<input type="text" class="form-control @error('title') is-invalid @enderror "
				id="track-name" placeholder="Song title" name="title"
				value="{{ request('title') ?? old('title') }}" @if($require===1) required @endif >
		</div>
	</div>
	<div class="col-sm-6">
		<label for="artist-name" class="form-label">Artist</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
			<input type="text" class="form-control @error('artist') is-invalid @enderror "
				id="artist-name" placeholder="Artist" name="artist"
				value="{{ request('artist') ?? old('artist') }}">
		</div>
	</div>
	<div class="col-sm-6">
		<label for="album-name" class="form-label">Album</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text">
				<i class="fa-solid fa-compact-disc"></i>
			</span>
			<input type="text" class="form-control @error('album') is-invalid @enderror "
				id="album-name" placeholder="Album" name="album"
				value="{{ request('album') ?? old('album') }}">
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		<a href="{{ route($provider . '.index') }}">Basic search</a>
	</small>
</form>
