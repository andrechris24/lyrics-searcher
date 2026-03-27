<div class="alert alert-danger alert-dismissible" role="alert">
	<i class="fas fa-circle-xmark"></i>
	{{ Session::get('error') ?? 'Error validating input:' }}
	@if ($errors->any())
		<ul>
			@foreach ($errors->all() as $err)
				<li>{{ $err }}</li>
			@endforeach
		</ul>
	@endif
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
