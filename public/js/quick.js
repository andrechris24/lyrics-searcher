/* global blobDL, Swal, toast, swalConfirm, formatSeconds */
let plainContents,
	syncedContents,
	fileName,
	formData,
	track_id,
	meta,
	localContents,
	wbwContents,
	ext;
const mxPlainDL = document.getElementById("download-link-mx-plain"),
	mxSyncedDL = document.getElementById("download-link-mx-synced"),
	mxRichsyncDL = document.getElementById("download-link-mx-richsync"),
	llPlainDL = document.getElementById("download-link-lrclib-plain"),
	llSyncedDL = document.getElementById("download-link-lrclib-synced"),
	wbwDL = document.getElementById("download-link-lrclib-wbw"),
	localDL = document.getElementById("download-link-local");
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});
$("#searchSongLyric").on("submit", function (e) {
	e.preventDefault();
	formData = $("#searchSongLyric").serializeArray();
	$.ajax({
		data: $("#searchSongLyric").serialize(),
		url: "/result",
		beforeSend: function () {
			$(":input").removeClass("is-invalid");
			$("#searchSongLyric :input").prop("disabled", true);
			$.LoadingOverlay("show");
		},
		complete: function () {
			$("#searchSongLyric :input").prop("disabled", false);
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if (data.instrumental === true || data.instrumental === 1) {
				toast.fire({
					icon: "info",
					text: `Found song ${data.artist} - ${data.title} but it's marked as Instrumental`,
				});
			} else {
				if (data.source !== "lyrics.ovh") {
					fileName = `${data.artist} - ${data.title}`;
					meta = `\n[ar: ${data.artist}]\n[ti: ${data.title}]\n[al: ${data.album}]\n`;
					if (data.source === "local") {
						if (!data.content.match(/\[(\d+):(\d+).(\d+)\]/))
							localContents = data.content;
						else {
							localContents =
								`[id: ${data.id}]${meta}[length: ${formatSeconds(data.duration)}]\n` +
								`[by: ${data.user.name}]\n[offset: ${data.offset}]\n${data.content}`;
						}
					} else {
						plainContents = `${fileName}\n\n${data.plain}`;
						if (data.synced === "" || data.synced === null) {
							if (data.source === "lrclib")
								llSyncedDL.classList.add("disabled");
							else mxSyncedDL.classList.add("disabled");
							syncedContents = null;
						} else {
							if (data.source === "lrclib")
								llSyncedDL.classList.remove("disabled");
							else mxSyncedDL.classList.remove("disabled");
							syncedContents = `[id: ${data.id}]${meta}[length: ${data.duration}]\n[by: ${data.source}]\n${data.synced}`;
						}
					}
				}
				$(".search-term").text(
					`${formData[2].value} - ${formData[0].value} ${
						formData[3].value !== "" ? `(${formData[3].value})` : ""
					}`
				);
				switch (data.source) {
					case "lrclib":
						$("#lrclib-content").text(data.plain);
						$("#lrclib-song-artist").text(data.artist);
						$("#lrclib-song-title").text(data.title);
						$("#lrclib-song-album").text(data.album);
						$("#lrclib-song-duration").text(data.duration);
						if (data.wbw === null || data.wbw === "") {
							$("#lrclib-wbw").addClass("d-none");
							wbwContents = null;
						} else {
							$("#lrclib-wbw").removeClass("d-none");
							wbwContents = data.wbw;
						}
						$("#modalLRCLib").modal("show");
						break;
					case "musixmatch":
						if (data.art800 !== "" && data.art800 !== null)
							$("#song-art").attr("src", data.art800);
						else if (data.art500 !== "" && data.art500 !== null)
							$("#song-art").attr("src", data.art500);
						else if (data.art350 !== "" && data.art350 !== null)
							$("#song-art").attr("src", data.art350);
						else if (data.art100 !== "" && data.art100 !== null)
							$("#song-art").attr("src", data.art100);
						else {
							$("#song-art").attr(
								"src",
								`https://placehold.co/500?text=${encodeURIComponent(data.album)}`
							);
						}
						if (data.spotify === "" || data.spotify === null)
							$("#spotify-btn").prop("disabled", true);
						else {
							$("#spotify-btn").prop("disabled", false);
							$("#spotify-btn").attr(
								"href",
								`https://open.spotify.com/track/${data.spotify}`
							);
						}
						if (data.richsync === true || data.richsync === 1) {
							track_id = data.track_id;
							mxRichsyncDL.classList.remove("disabled");
						} else {
							track_id = null;
							mxRichsyncDL.classList.add("disabled");
						}
						$("#mx-plain-lyrics-content").text(data.plain);
						$("#mx-song-artist").text(data.artist);
						$("#mx-song-title").text(
							data.title + (data.explicit === 1 ? " [E]" : "")
						);
						$("#mx-song-album").text(data.album);
						$("#mx-song-duration").text(data.duration);
						$("#song-release-date").text(data.release);
						$("#song-last-update").text(data.updated);
						$("#song-copyright").text(data.copyright);
						$("#musixmatch-btn").attr("href", data.share);
						$("#modalMX").modal("show");
						break;
					case "lyrics.ovh":
						$("#lyrics-ovh-content").text(data.content);
						$("#modalLyricsOVH").modal("show");
						break;
					case "local":
						$("#local-content").text(data.content);
						$("#local-song-artist").text(data.artist);
						$("#local-song-title").text(data.title);
						$("#local-song-album").text(data.album);
						$("#local-song-duration").text(
							`${formatSeconds(data.duration)} (offset: ${data.offset})`
						);
						$("#lyric-by").text(data.user.name);
						$("#modalLocal").modal("show");
						break;
					default:
						toast.fire({
							icon: "error",
							text: "Unsupported source"
						});
						break;
				}
			}
		},
		error: function (xhr, st, err) {
			console.warn(err);
			if (xhr.status === 422) {
				if (typeof xhr.responseJSON.errors.title !== "undefined")
					$("#track-name").addClass("is-invalid");
				if (typeof xhr.responseJSON.errors.artist !== "undefined")
					$("#artist-name").addClass("is-invalid");
				if (typeof xhr.responseJSON.errors.album !== "undefined")
					$("#album-name").addClass("is-invalid");
				if (typeof xhr.responseJSON.errors.source !== "undefined")
					$("#lyric-source").addClass("is-invalid");
			}
			toast.fire({
				icon: "error",
				titleText:
					typeof xhr.responseJSON.source !== "undefined"
						? xhr.responseJSON.source
						: "",
				text: st==='timeout'?'Connection timed out': xhr.responseJSON?.message??err??st
			});
		}
	});
});
mxPlainDL.onclick = function () {
	mxPlainDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(plainContents)}`;
	mxPlainDL.download = `${fileName}.txt`;
};
mxSyncedDL.onclick = function () {
	mxSyncedDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(syncedContents)}`;
	mxSyncedDL.download = `${fileName}.lrc`;
};
mxRichsyncDL.onclick = function (e) {
	e.preventDefault();
	swalConfirm
		.fire({
			title: "Download Richsync lyric?",
			text: "Musixmatch richsync lyric is an either word-by-word or syllable version of synced lyric and not all players are supported.",
			customClass: {
				confirmButton: "btn btn-primary btn-lg me-2",
				cancelButton: "btn btn-danger btn-lg"
			},
			cancelButtonText: "No",
			preConfirm: async function () {
				try {
					const response = await $.ajax({
						url: `/musixmatch/${track_id}/richsync`,
						success: function (data) {
							return JSON.stringify(data);
						},
						error: function (xhr, st, err) {
							throw new Error(xhr.responseJSON?.message ?? err ?? st);
						}
					});
					return response;
				} catch (e) {
					console.warn(e);
					Swal.showValidationMessage(
						`Download failed: ${e.responseJSON?.message ?? "Server connection was lost"}`
					);
				}
			}
		})
		.then((result) => {
			if (result.isConfirmed) {
				blobDL(
					`[id: ${result.value.id}]${meta}[length: ${result.value.duration}]\n[by: Musixmatch (Richsync)]\n${result.value.content}`,
					`${fileName}.lrc`
				);
			}
		});
};
llPlainDL.onclick = function () {
	llPlainDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(plainContents)}`;
	llPlainDL.download = `${fileName}.txt`;
};
llSyncedDL.onclick = function () {
	llSyncedDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(syncedContents)}`;
	llSyncedDL.download = `${fileName}.lrc`;
};
wbwDL.onclick = function (e) {
	e.preventDefault();
	swalConfirm
		.fire({
			title: "Convert to LRC format?",
			text: "LRCLib's Word-by-word lyric is in YAML format and only a few players supported. Convert to LRC?",
			customClass: {
				confirmButton: "btn btn-primary btn-lg me-2",
				denyButton: "btn btn-danger btn-lg me-2",
				cancelButton: "btn btn-warning btn-lg"
			},
			denyButtonText: "No",
			showDenyButton: true,
			preConfirm: async function () {
				try {
					const response = await $.ajax({
						headers: {
							"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
						},
						type: "POST",
						url: "/lrclib/convert",
						data: {
							content: wbwContents
						},
						success: function (data) {
							return JSON.stringify(data);
						},
						error: function (xhr, st, err) {
							throw new Error(xhr.responseJSON?.message ?? err ?? st);
						}
					});
					return response;
				} catch (e) {
					console.warn(e);
					Swal.showValidationMessage(
						`Failed to convert: ${e.responseJSON?.message ?? "Server connection was lost"}`
					);
				}
			}
		})
		.then((result) => {
			if (result.isConfirmed) {
				if (result.value.instrumental === true)
					toast.fire({
						icon: "warning",
						text: "Conversion aborted, song is Instrumental"
					});
				else blobDL(result.value.lrc, `${fileName}.lrc`);
			} else if (result.isDenied) blobDL(wbwContents, `${fileName}.yaml`);
		});
};
localDL.onclick = function () {
	if (!localContents.match(/\[(\d+):(\d+).(\d+)\]/)) {
		ext = ".txt";
		blobDL(`${fileName}\n\n${localContents}`, fileName + ext);
	} else {
		ext = ".lrc";
		if (localContents.match(/<(\d+):(\d+).(\d+)>/g)) {
			Swal.fire({
				icon: "question",
				title:
					"This lyric contains syllable timestamps and only a few players supports this type. Do you want to keep them?",
				theme: "bootstrap-5",
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: "Yes",
				denyButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					blobDL(localContents, fileName + ext);
				} else if (result.isDenied) {
					const syncedContent = localContents.replace(
						/<(\d+):(\d+).(\d+)>/g,
						""
					);
					blobDL(syncedContent, fileName + ext);
				} else console.warn("Download cancelled");
			});
		} else blobDL(localContents, fileName + ext);
	}
};
