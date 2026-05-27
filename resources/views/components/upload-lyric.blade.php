<div class="modal fade" tabindex="-1" id="modalUploadLyric"
	aria-labelledby="modalUploadLyricLabel" role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalUploadLyric" class="modal-title">Upload Lyrics</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form method="POST" id="uploadLyricForm" enctype="multipart/formdata">
					<label for="lyric-file" class="form-label">
						Select lyric file to upload
					</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-solid fa-file-lines"></i></span>
						<input type="file" class="form-control" id="lyric-file"
							accept=".lrc, .elrc, .txt" name="lrc[]" multiple required>
					</div>
					<div class="form-text">This form expects the file to contain artist, title, album,
						and duration info. Otherwise, file name will be used in format Artist - Title.
						Uploaded lyrics can be edited in admin panel.</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					Close
				</button>
				<button class="btn btn-primary" type="submit" form="uploadLyricForm">
					Upload
				</button>
			</div>
		</div>
	</div>
</div>
