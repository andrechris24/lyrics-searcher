<form action="{{ route($provider . '.search') }}" class="row g-3 mb-3">
	<div class="input-group input-group-lg">
		<span class="input-group-text">
			<i class="fa-solid fa-magnifying-glass"></i>
		</span>
		<div class="form-floating">
			<input type="search" name="query" placeholder="Enter search query here..."
				class="form-control form-control-lg @error('query') is-invalid @enderror "
				value="{{ request('query') ?? old('query') }}" id="basic-search-query" required
				@if (empty(request('query'))) autofocus @endif>
			<label  for="basic-search-query">Search query</label>
		</div>
		<button type="submit" class="btn btn-primary">Search</button>
	</div>
	@if (in_array($provider, ['lrclib', 'musixmatch', 'kugou']))
		<small class="form-text">
			<a href="{{ route($provider . '.advanced') }}">Advanced search</a>
		</small>
	@elseif(in_array($provider, ['spotify', 'youtube']))
		<small class="form-text">Powered by Lyrically API</small>
	@endif
</form>
