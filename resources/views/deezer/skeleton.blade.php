<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-3 d-none" id="deezer-loader">
	@for ($a = 0; $a < 4; $a++)
		<div @class([
			'col',
			'd-none' => $a !== 0,
			'd-md-block' => $a === 1,
			'd-lg-block' => $a > 1
		])>
			<div class="card" aria-hidden="true">
				<img src="https://placehold.co/500?text=Loading%20results" class="card-img-top">
				<div class="card-header placeholder-glow">
					<span class="placeholder col-12"></span>
				</div>
				<div class="card-body">
					<h5 class="card-title placeholder-glow">
						<span class="placeholder col-12"></span>
					</h5>
					<p class="card-text placeholder-glow">
						<span class="placeholder col-12"></span>
					</p>
					<small class="card-text text-muted placeholder-glow">
						<span class="placeholder col-12"></span>
					</small>
				</div>
				<div class="card-footer">
					<div class="btn-group" role="group">
						<button type="button" class="btn btn-primary" disabled>
							<i class="fa-solid fa-eye"></i>
						</button>
						<button type="button" class="btn btn-info" disabled>
							<i class="fa-solid fa-play"></i>
						</button>
						<a href="#" class="btn btn-success disabled" aria-disabled="true">
							<i class="fa-brands fa-deezer"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	@endfor
</div>
