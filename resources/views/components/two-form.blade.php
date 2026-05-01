<form class="row g-3 mb-3" 
	action="{{ route($provider . ($type==='advanced'?'.search.advanced':'.search')) }}">
	<div class="col-sm-6">
		<label for="track-name" class="form-label">
			Song Title <span class="text-danger"><b>*</b></span>
		</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
			<input type="text" class="form-control @error('title') is-invalid @enderror "
				id="track-name" placeholder="Song title" name="title"
				value="{{ request('title') ?? old('title') }}" required
				@if(request()->routeIs('*.index')) autofocus @endif >
		</div>
	</div>
	<div class="col-sm-6">
		<label for="artist-name" class="form-label">
			Artist <span class="text-danger"><b>*</b></span>
		</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
			<input type="text" class="form-control @error('artist') is-invalid @enderror "
				id="artist-name" placeholder="Artist" name="artist"
				value="{{ request('artist') ?? old('artist') }}" required>
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		@if($type==='advanced')
		<a href="{{ route($provider . '.index') }}">Basic search</a>
		@else
		<a href="{{ route($provider . '.advanced') }}">Advanced search</a>
		@endif
	</small>
</form>
