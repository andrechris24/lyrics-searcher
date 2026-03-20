<form action="{{ route($provider . '.search') }}" class="row g-3 mb-3">
	<div class="input-group">
		<span class="input-group-text">
			<i class="fa-solid fa-magnifying-glass"></i>
		</span>
		<input type="search" name="query" placeholder="Enter search query here..."
			class="form-control form-control-lg @error('query') is-invalid @enderror "
			value="{{ request('query') ?? old('query') }}" required @if(empty(request('query'))) autofocus @endif >
		<button type="submit" class="btn btn-primary">Search</button>
	</div>
	@if (in_array($provider, ['lrclib', 'musixmatch']))
		<small class="form-text">
			<a href="{{ route($provider . '.advanced') }}">Advanced search</a>
		</small>
	@endif
</form>
