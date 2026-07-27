<form class="row g-3 mb-3" id="advanced-search-form" action="">
	<div class="col-12">
		<div class="input-group input-group-lg">
			<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
			<div class="form-floating">
				<input type="text" class="form-control" id="track-name" placeholder="Song title"
					name="title" value="{{ request('title') ?? old('title') }}" @required($require === 1)
					autofocus>
				<label for="track-name" class="form-label">
					Song Title @if ($require === 1)
						<span class="text-danger"><b>*</b></span>
					@endif
				</label>
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
	<div class="col-sm-6">
		<div class="input-group input-group-lg">
			<span class="input-group-text">
				<i class="fa-solid fa-compact-disc"></i>
			</span>
			<div class="form-floating">
				<input type="text" class="form-control" id="album-name" placeholder="Album"
					name="album" value="{{ request('album') ?? old('album') }}">
				<label for="album-name" class="form-label">Album</label>
			</div>
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		<a href="{{ route($provider . '.index') }}">Basic search</a>
	</small>
</form>
