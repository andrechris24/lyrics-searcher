@extends('layout')
@section('title', 'Home')
@section('content')
	<div class="px-5 mx-5 py-5 my-5">
		<h3 class="text-center">Lyrics Searcher by andrechris24</h3>
		<p>Welcome to andrechris24's Lyrics Searcher! This site provides synchronized lyrics search from Kugou, NetEase, QQ Music, Musixmatch, LRCLib, Soda Music, and on local server. This form below is a quick search to 4 providers (might be inaccurate).</p>
		@if (Session::has('error') || $errors->any())
			<x-error />
		@endif
		<x-no-script />
		<form class="row g-3 mb-3" action="{{ route('result') }}" id="searchSongLyric">
			<div class="col-12 col-md-8">
				<label for="track-name" class="form-label">
					Song Title <span class="text-danger"><b>*</b></span>
				</label>
				<div class="input-group input-group-lg">
					<span class="input-group-text"><i class="fa-solid fa-music"></i></span>
					<input type="text" class="form-control" id="track-name" placeholder="Song title"
						name="title" required autofocus>
				</div>
			</div>
			<div class="col-12 col-md-4">
				<label for="lyric-source" class="form-label">
					Source <span class="text-danger"><b>*</b></span>
				</label>
				<select class="form-select form-select-lg" name="source" id="lyric-source" required>
					<option value="" selected>Choose</option>
					<option value="musixmatch" @empty(env('MUSIXMATCH_TOKEN')) disabled @endempty >
						Musixmatch
					</option>
					<option value="lrclib">LRCLib</option>
					<option value="plains">Lyrics.ovh</option>
					<option value="local">Local</option>
				</select>
			</div>
			<div class="col-12 col-sm-6">
				<label for="artist-name" class="form-label">
					Artist <span class="text-danger"><b>*</b></span>
				</label>
				<div class="input-group input-group-lg">
					<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
					<input type="text" class="form-control" id="artist-name" placeholder="Artist"
						name="artist" required>
				</div>
			</div>
			<div class="col-12 col-sm-6">
				<label for="album-name" class="form-label">Album</label>
				<div class="input-group input-group-lg">
					<span class="input-group-text">
						<i class="fa-solid fa-compact-disc"></i>
					</span>
					<input type="text" class="form-control" id="album-name" placeholder="Album"
						name="album">
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Search</button>
		</form>
	</div>
	<div class="modal fade" tabindex="-1" id="modalMX" aria-labelledby="modalMXLabel"
		role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalMXLabel" class="modal-title">
						Musixmatch Result for <span class="search-term">...</span>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-md-4">
							<img src="" class="img-fluid" id="song-art">
						</div>
						<div class="col-12 col-md-8">
							<div class="row mb-2">
								<div class="col-4"><b>Title</b></div>
								<div class="col-8"><span id="mx-song-title">...</span></div>
								<div class="col-4"><b>Artist</b></div>
								<div class="col-8"><span id="mx-song-artist">...</span></div>
								<div class="col-4"><b>Album</b></div>
								<div class="col-8"><span id="mx-song-album">-</span></div>
								<div class="col-4"><b>Duration</b></div>
								<div class="col-8"><span id="mx-song-duration"></span></div>
								<div class="col-4"><b>Released</b></div>
								<div class="col-8"><span id="song-release-date"></span></div>
								<div class="col-4"><b>Last Update</b></div>
								<div class="col-8"><span id="song-last-update"></span></div>
							</div>
							<p id="song-copyright"></p>
							<div class="btn-group" role="group">
								<a href="#" class="btn btn-success" target="_blank" id="spotify-btn">
									<i class="fa-brands fa-spotify"></i> Spotify
								</a>
								<a href="#" class="btn btn-warning" target="_blank" id="musixmatch-btn">
									<i class="fa-solid fa-music"></i> Musixmatch
								</a>
							</div>
						</div>
					</div>
					<p id="mx-plain-lyrics-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						Close
					</button>
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button"
							data-bs-toggle="dropdown" aria-expanded="false">
							Save to Device
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="dropdown-item" href="#" id="download-link-mx-plain">Plain</a>
							</li>
							<li>
								<a class="dropdown-item" href="#" id="download-link-mx-synced">Synced</a>
							</li>
							<li>
								<a class="dropdown-item" href="#"
									id="download-link-mx-richsync">Word-by-word</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<x-lrclib-modal text="LRCLib Result for "/>
	<div class="modal fade" tabindex="-1" id="modalLyricsOVH"
		aria-labelledby="modalLyricsOVHLabel" role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLyricsOVHLabel" class="modal-title">
						Lyrics.ovh Result for <span class="search-term">...</span>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						Lyrics.ovh returns lyrics from either Genius, AZLyrics, Paroles.net,
						LyricsMania, Letras.mus.br, and Lyrics.com in plain format only,
						without artist, title, and album information.
					</div>
					<p id="lyrics-ovh-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						Close
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" tabindex="-1" id="modalLocal" aria-labelledby="modalLocalLabel"
	role="dialog" aria-hidden="true">
		<div role="document"
			class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalLocalLabel" class="modal-title">
						Local search results for <span class="search-term"></span>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-12 col-md-4">
							<b>Artist</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-artist">...</span>
						</div>
						<div class="col-12 col-md-4">
							<b>Title</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-title">...</span>
						</div>
						<div class="col-12 col-md-4">
							<b>Album</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-album">-</span>
						</div>
						<div class="col-12 col-md-4">
							<b>Duration</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="local-song-duration">--:--</span>
						</div>
						<div class="col-12 col-md-4">
							<b>By</b>
						</div>
						<div class="col-12 col-md-8">
							<span id="lyric-by">?</span>
						</div>
					</div>
					<p id="local-content" style="white-space: pre-line"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						Close
					</button>
					<button class="btn btn-primary" type="button" id="download-link-local">
						Save
					</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('js')
	<script type="text/javascript">
		let plainContents, syncedContents, fileName, formData, message, track_id, meta, plainContent, localContent, localContents;
		const mxPlainDL = document.getElementById("download-link-mx-plain"),
			mxSyncedDL = document.getElementById("download-link-mx-synced"),
			mxRichsyncDL = document.getElementById("download-link-mx-richsync"),
			llPlainDL = document.getElementById("download-link-lrclib-plain"),
			llSyncedDL = document.getElementById("download-link-lrclib-synced"),
			localDL=document.getElementById("download-link-local");
		$("#searchSongLyric").on('submit', function(e) {
			e.preventDefault();
			formData=$("#searchSongLyric").serializeArray();
			$.ajax({
				data: $("#searchSongLyric").serialize(),
				url: "{{ route('result') }}",
				beforeSend: function() {
					$(":input").removeClass('is-invalid');
					$("#searchSongLyric :input").prop('disabled', true);
					$.LoadingOverlay("show");
				},
				complete: function() {
					$("#searchSongLyric :input").prop('disabled', false);
					$.LoadingOverlay("hide");
				},
				success: function(data) {
					if (data.instrumental === true || data.instrumental ===1) {
						toast.fire({
							icon: 'info',
							text: `Found song ${data.artist} - ${data.title} but it's marked as Instrumental`
						});
					} else {
						if (data.source !== 'lyrics.ovh') {
							fileName = `${data.artist} - ${data.title}`;
							meta =
								`\n[ar: ${data.artist}]\n[ti: ${data.title}]\n[al: ${data.album}]\n`;
							if(data.source==='local'){
								{{-- localContent=data.content.replace(/\r\n/g,"<br/>"); --}}
								localContents=
									`[id: ${data.id}]${meta}[length: ${formatSeconds(data.duration)}]\n`+
									`[by: ${data.by}]\n[offset: ${data.offset}]\n${data.content}`;
								plainContent=null;
							}else{
								plainContent = data.plain.replace(/\n/g,"<br/>");
								plainContents = `${fileName}\n\n${data.plain}`;
								{{-- localContent=null; --}}
								if (data.synced === "" || data.synced === null) {
									if (data.source === 'lrclib') 
										llSyncedDL.classList.add("disabled");
									else mxSyncedDL.classList.add("disabled");
									syncedContents = null;
								} else {
									if (data.source === 'lrclib') 
										llSyncedDL.classList.remove("disabled");
									else mxSyncedDL.classList.remove("disabled");
									syncedContents =
										`[id: ${data.id}]${meta}[length: ${data.duration}]\n[by: ${data.source}]\n${data.synced}`;
								}
							}
						}
						$(".search-term").text(`${formData[2].value} - ${formData[0].value} ` + 
							(formData[3].value !== '' ? `(${formData[3].value})` : ''));
						switch (data.source) {
							case 'lrclib':
								$("#lrclib-content").html(plainContent);
								$("#lrclib-song-artist").text(data.artist);
								$("#lrclib-song-title").text(data.title);
								$("#lrclib-song-album").text(data.album);
								$("#lrclib-song-duration").text(data.duration);
								$("#modalLRCLib").modal('show');
								break;
							case 'musixmatch':
								if (data.art800 !== '' && data.art800 !==null)
									$("#song-art").attr('src', data.art800);
								else if (data.art500 !== '' && data.art500 !==null)
									$("#song-art").attr('src', data.art500);
								else if (data.art350 !== '' && data.art350 !==null)
									$("#song-art").attr('src', data.art350);
								else if (data.art100 !== '' && data.art100 !==null)
									$("#song-art").attr('src', data.art100);
								else {
									$("#song-art").attr('src',
										`https://placehold.co/500?text=${encodeURIComponent(data.album)}`
									);
								}
								if (data.spotify === '' || data.spotify ===null)
									$("#spotify-btn").prop('disabled', true);
								else {
									$("#spotify-btn").prop('disabled',false);
									$("#spotify-btn").attr('href',
										`https://open.spotify.com/track/${data.spotify}`
									);
								}
								if (data.richsync === true || data.richsync === 1) {
									track_id = data.track_id;
									mxRichsyncDL.classList.remove(
										'disabled');
								} else {
									track_id = null;
									mxRichsyncDL.classList.add('disabled');
								}
								$("#mx-plain-lyrics-content").html(
									plainContent);
								$("#mx-song-artist").text(data.artist);
								$("#mx-song-title").text(data.title + (data
									.explicit === 1 ? ' [E]' : ''));
								$("#mx-song-album").text(data.album);
								$("#mx-song-duration").text(data.duration);
								$("#song-release-date").text(data.release);
								$("#song-last-update").text(data.updated);
								$("#song-copyright").text(data.copyright);
								$("#musixmatch-btn").attr('href', data.share);
								$("#modalMX").modal('show');
								break;
							case 'lyrics.ovh':
								$("#lyrics-ovh-content").html(data.content);
								$("#modalLyricsOVH").modal('show');
								break;
							case 'local':
								$("#local-content").html(data.content);
								$("#local-song-artist").text(data.artist);
								$("#local-song-title").text(data.title);
								$("#local-song-album").text(data.album);
								$("#local-song-duration").text(
									`${formatSeconds(data.duration)} (offset: ${data.offset})`
								);
								$("#lyric-by").text(data.by);
								$("#modalLocal").modal('show');
								break;
							default:
								toast.fire({icon: 'error',text: "Unsupported source"});
								break;
						}
					}
				},
				error: function(xhr, st) {
					if (xhr.status === 422) {
						if (typeof xhr.responseJSON.errors.title !==
							"undefined")
							$("#track-name").addClass('is-invalid');
						if (typeof xhr.responseJSON.errors.artist !==
							"undefined")
							$("#artist-name").addClass('is-invalid');
						if (typeof xhr.responseJSON.errors.album !==
							"undefined")
							$("#album-name").addClass('is-invalid');
						if (typeof xhr.responseJSON.errors.source !==
							"undefined")
							$("#lyric-source").addClass('is-invalid');
					}
					if (st === 'timeout') message = "Connection timed out";
					else message = xhr.responseJSON.message ?? st;
					toast.fire({icon: "error",text: message});
				}
			});
		});
		mxPlainDL.onclick = function() {
			mxPlainDL.href =
				`data:text/plain;charset=utf-8,${encodeURIComponent(plainContents)}`;
			mxPlainDL.download = `${fileName}.txt`;
		};
		mxSyncedDL.onclick = function() {
			mxSyncedDL.href =
				`data:text/plain;charset=utf-8,${encodeURIComponent(syncedContents)}`;
			mxSyncedDL.download = `${fileName}.lrc`;
		};
		mxRichsyncDL.onclick = function(e) {
			e.preventDefault();
			$.ajax({
				url: `/musixmatch/${track_id}/richsync`,
				beforeSend: function() {
					$.LoadingOverlay("show");
				},
				complete: function() {
					$.LoadingOverlay("hide");
				},
				success: function(data) {
					blobDL(
						`[id: ${data.id}]${meta}[length: ${data.duration}]\n[by: Musixmatch (Word-by-Word)]\n${data.content}`,
						`${fileName}.lrc`
					);
				},
				error: function(xhr, st) {
					if (st === "timeout") message = "Connection timed out";
					else message = xhr.responseJSON.message ?? st;
					toast.fire({
						icon: "error",
						text: message
					});
				}
			});
		};
		llPlainDL.onclick = function() {
			llPlainDL.href =
				`data:text/plain;charset=utf-8,${encodeURIComponent(plainContents)}`;
			llPlainDL.download = `${fileName}.txt`;
		};
		llSyncedDL.onclick = function() {
			llSyncedDL.href =
				`data:text/plain;charset=utf-8,${encodeURIComponent(syncedContents)}`;
			llSyncedDL.download = `${fileName}.lrc`;
		};
		localDL.onclick = function() {
			blobDL(localContents,`${fileName}.lrc`);
		};
		function formatSeconds(s) {
			// Validate input
			if (typeof s !== "number" || isNaN(s) || s < 0) 
				return "00:00"; // Default for invalid input

			// Calculate minutes and seconds
			const minutes = Math.floor(s / 60);
			const seconds = s % 60;

			// Pad with leading zeros if needed
			const formattedMinutes = String(minutes).padStart(2, "0");
			const formattedSeconds = String(seconds).padStart(2, "0");

			return `${formattedMinutes}:${formattedSeconds}`;
		}
	</script>
@endsection
