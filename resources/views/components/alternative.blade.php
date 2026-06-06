<form class="row g-3 mb-3" action="{{ route($provider . '.search.advanced') }}">
	<div class="col-md-7">
		<div class="input-group input-group-lg">
			<span class="input-group-text">
				<i class="fa-solid fa-magnifying-glass"></i>
			</span>
			<div class="form-floating">
				<input type="search" name="query" placeholder="Enter search query here..."
					class="form-control form-control-lg @error('query') is-invalid @enderror "
					value="{{ request('query') ?? old('query') }}" id="query-input" required
					@if (empty(request('query'))) autofocus @endif>
				<label for="query-input" class="form-label">Query</label>
			</div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="input-group input-group-lg">
			<span class="input-group-text">Duration</span>
			<input type="number" name="minutes" min="0" max="199" title="Minutes" 
				class="form-control @error('minutes') is-invalid @enderror " data-bs-toggle="tooltip"
				value="{{ request('minutes') ?? (old('minutes') ?? 0) }}" required>
			<input type="number" name="seconds" min="0" max="59" title="Seconds" 
				class="form-control @error('seconds') is-invalid @enderror " data-bs-toggle="tooltip"
				value="{{ request('seconds') ?? (old('seconds') ?? 0) }}" required>
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Search</button>
	<small class="form-text">
		<a href="{{ route($provider . '.index') }}">Basic search</a>
	</small>
</form>
