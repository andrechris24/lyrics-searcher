<div class="modal fade" tabindex="-1" id="modalLyrics" aria-labelledby="modalLyricsLabel"
	role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalLyricsLabel" class="modal-title">Preview lyric</h5>
				<button type="button" class="btn-close" data-coreui-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<x-lyrically />
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
						<b>Album</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-album">-</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="song-duration">--:--</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Writers</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-writers"></span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Copyright</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-copyright"></span>
					</div>
					<div class="col-12 col-sm-4">
						<b>License</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-license"></span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Lyric Type</b>
					</div>
					<div class="col-12 col-sm-8">
						<p class="placeholder-glow d-none">
							<span class="placeholder col-12"></span>
						</p>
						<span id="song-lyric-type"></span>
					</div>
				</div>
				<p class="placeholder-glow d-none">
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
					<span class="placeholder col-12"></span>
				</p>
				<p id="lyrics-content" style="white-space: pre-line"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
					Close
				</button>
				<div class="dropdown">
					<button class="btn btn-primary dropdown-toggle" type="button"
						data-coreui-toggle="dropdown" aria-expanded="false">
						Save
					</button>
					<ul class="dropdown-menu">
						<li>
							<a class="dropdown-item" href="#" id="dl-plain">Plain</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" id="dl-synced">Synced</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" id="dl-syllyric"
								data-coreui-toggle="tooltip"
								data-coreui-title="Not all players supports Syllable lyrics, use with compatible players like OuterTune (Android) or BetterLyrics (Windows)">
								Syllable
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modalPreviewSong"
	aria-labelledby="modalPreviewSongLabel" role="dialog" aria-hidden="true">
	<div role="document"
		class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalPreviewSongLabel" class="modal-title">Preview Song</h5>
				<button type="button" class="btn-close" data-coreui-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-12 col-sm-4">
						<b>Artist</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-artist">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Title</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-title">...</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Album</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-album">-</span>
					</div>
					<div class="col-12 col-sm-4">
						<b>Duration</b>
					</div>
					<div class="col-12 col-sm-8">
						<span id="preview-duration">--:--</span>
					</div>
				</div>
				<audio controls id="preview-player">
					<source id="preview-song" src="">
					Your browser does not support the audio element.
				</audio>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
					Close
				</button>
			</div>
		</div>
	</div>
</div>
<x-no-script />
