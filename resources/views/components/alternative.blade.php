<form class="row g-3 mb-3" action="{{ route($provider . '.search.advanced') }}">
	<div class="col-md-8">
		<label for="query-input" class="form-label">Query</label>
		<div class="input-group input-group-lg">
			<span class="input-group-text">
				<i class="fa-solid fa-magnifying-glass"></i>
			</span>
			<input type="search" name="query" placeholder="Enter search query here..."
				class="form-control form-control-lg @error('query') is-invalid @enderror "
				value="{{ request('query') ?? old('query') }}" id="query-input" required
				@if (empty(request('query'))) autofocus @endif>
		</div>
	</div>
	<div class="col-md-4">
		<label for="song-duration" class="form-label">Duration</label>
		<div class="input-group input-group-lg" id="song-duration">
			<input type="number" name="minutes" min="0" max="59"
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
