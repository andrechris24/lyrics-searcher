<div class="modal fade" tabindex="-1" id="modalLRCLib" aria-labelledby="modalLRCLibLabel"
	role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalLRCLibLabel" class="modal-title">{{$text}}<span class="search-term"></span></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-12 col-md-4">
						<b>Artist</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="lrclib-song-artist">...</span>
					</div>
					<div class="col-12 col-md-4">
						<b>Title</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="lrclib-song-title">...</span>
					</div>
					<div class="col-12 col-md-4">
						<b>Album</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="lrclib-song-album">-</span>
					</div>
					<div class="col-12 col-md-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-md-8">
						<span id="lrclib-song-duration">--:--</span>
					</div>
				</div>
				<p id="lrclib-content" style="white-space: pre-line"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					Close
				</button>
				<div class="dropdown">
					<button class="btn btn-primary dropdown-toggle" type="button"
						data-bs-toggle="dropdown" aria-expanded="false">
						Save
					</button>
					<ul class="dropdown-menu">
						<li>
							<a class="dropdown-item" href="#" id="download-link-lrclib-plain">Plain</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" id="download-link-lrclib-synced">Synced</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>