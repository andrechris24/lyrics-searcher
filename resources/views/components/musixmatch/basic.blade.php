<form action="{{ route('musixmatch.search') }}" class="row g-3 mb-3">
	<div class="row g-2">
		<div class="col-md-8">
			<div class="input-group input-group-lg mb-3">
				<span class="input-group-text">
					<i class="fa-solid fa-magnifying-glass"></i>
				</span>
				<div class="form-floating">
					<input type="search" name="query" placeholder="Enter search query here..."
						class="form-control form-control-lg @error('query') is-invalid @enderror "
						value="{{ request('query') ?? old('query') }}" id="musixmatch-query" required
						@empty(request('query')) autofocus @endempty>
					<label for="musixmatch-query">Search query</label>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-floating">
				<select class="form-select @error('type') is-invalid @enderror " id="search-type"
					name="type" required>
					<option value="" @selected(empty(request('type'))&&empty(old('type')))>
					Select
					</option>
					<option value="all" @selected(in_array('all', [request('type'),old('type')]))>
					All
					</option>
					<option value="track" @selected(in_array('track', [request('type'),old('type')]))>
						Song title
					</option>
					<option value="artist" @selected(in_array('artist',[request('type'),old('type')]))>
					Artist
					</option>
					<option value="lyrics" @selected(in_array('lyrics',[request('type'),old('type')]))>
					Lyric
					</option>
					<option value="track_artist" @selected(in_array('track_artist',[request('type'),old('type')]))>
						Title and Artist
					</option>
					<option value="writer" @selected(in_array('writer', [request('type'),old('type')]))>
						Song writer
					</option>
				</select>
				<label for="search-type">Type</label>
			</div>
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		<a href="{{ route('musixmatch.advanced') }}">Advanced search</a>
	</small>
</form>
