<div class="modal fade" tabindex="-1" id="modalLyrics" aria-labelledby="modalLyricsLabel"
	role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalLyricsLabel" class="modal-title">View lyric</h5>
				<button type="button" class="btn-close" data-coreui-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-12 col-sm-4">
						<b>Artist</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-artist">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Title</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-title">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-duration"></span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Lyric Timing</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-lyric-type">?</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Uploaded by</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="lrc-owner"></span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Last update</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="lrc-update"></span>
					</div>
				</div>
				<p class="placeholder-glow d-none">
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
				</p>
				<p id="lyric-content" style="white-space: pre-line"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
					Close
				</button>
				<button type="button" class="btn btn-primary" id="save-btn">
					Save
				</button>
			</div>
		</div>
	</div>
</div>
