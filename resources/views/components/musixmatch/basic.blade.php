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
						@if (empty(request('query'))) autofocus @endif>
					<label for="musixmatch-query">Search query</label>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-floating">
				<select class="form-select @error('type') is-invalid @enderror " id="search-type" name="type" required>
					<option value="" {{request('type')===null?'selected':''}}>Select</option>
					<option value="all" {{request('type')==='all'?'selected':''}}>All</option>
					<option value="track" {{request('type')==='track'?'selected':''}}>
					Song title
					</option>
					<option value="artist" {{request('type')==='artist'?'selected':''}}>Artist</option>
					<option value="lyrics" {{request('type')==='lyrics'?'selected':''}}>Lyric</option>
					<option value="track_artist" {{request('type')==='track_artist'?'selected':''}}>
					Title and Artist
					</option>
					<option value="writer" {{request('type')==='writer'?'selected':''}}>
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
