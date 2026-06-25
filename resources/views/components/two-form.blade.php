<form class="row g-3 mb-3"
	action="{{ route($provider . ($type === 'advanced' ? '.search.advanced' : '.search')) }}">
	<div class="col-sm-6">
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
			<div class="form-floating">
				<input type="text" class="form-control @error('title') is-invalid @enderror "
					id="track-name" placeholder="Song title" name="title"
					value="{{ request('title') ?? old('title') }}" required
					@if (request()->routeIs('*.index')) autofocus @endif>
				<label for="track-name" class="form-label">
					Song Title <span class="text-danger"><b>*</b></span>
				</label>
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
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		@if ($type === 'advanced')
			<a href="{{ route($provider . '.index') }}">Basic search</a>
		@elseif(Illuminate\Support\Facades\Route::has($provider.'advanced'))
			<a href="{{ route($provider . '.advanced') }}">Advanced search</a>
		@endif
	</small>
</form>
