@if ($provider === 'kugou')
	<div class="alert alert-warning">
		Kugou's Advanced search is case sensitive, so please check word casing if no results.
	</div>
@endif
<form class="row g-3 mb-3" action="{{ route($provider . '.search.advanced') }}">
	<div class="col-12">
		<label for="track-name" class="form-label">
			Song Title <span class="text-danger"><b>*</b></span>
		</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
			<input type="text" class="form-control @error('title') is-invalid @enderror "
				id="track-name" placeholder="Song title" name="title"
				value="{{ request('title') ?? old('title') }}" required
				@if (request()->routeIs('*.index')) autofocus @endif>
		</div>
	</div>
	<div class="col-sm-6">
		<label for="artist-name" class="form-label">
			Artist <span class="text-danger"><b>*</b></span>
		</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
			<input type="text" class="form-control @error('artist') is-invalid @enderror "
				id="artist-name" placeholder="Artist" name="artist" required
				value="{{ request('artist') ?? old('artist') }}">
		</div>
	</div>
	<div class="col-sm-6">
		<label for="song-duration" class="form-label">Duration</label>
		<div class="input-group input-group-lg" id="song-duration">
			<input type="number" name="minutes" min="0" max="199"
				class="form-control @error('minutes') is-invalid @enderror "
				value="{{ request('minutes') ?? (old('minutes') ?? 0) }}" required>
			<span class="input-group-text">:</span>
			<input type="number" name="seconds" min="0" max="59"
				class="form-control @error('seconds') is-invalid @enderror "
				value="{{ request('seconds') ?? (old('seconds') ?? 0) }}" required>
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		<a href="{{ route($provider . '.index') }}">Basic search</a>
	</small>
</form>
