/* global blobDL, toast, Swal, swalConfirm, musicDL */
let plainContents, syncedContents, fileName, track_id, meta;
const plainDL = document.getElementById("download-link-plain"),
	syncedDL = document.getElementById("download-link-synced"),
	richsyncDL = document.getElementById("download-link-richsync"),
	lyricsModal = document.getElementById("modalSpotify");
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});
$(".download-btn").on("click", function () {
				Swal.fire({
					title: "Download song?",
					text: "This will download the song from Spotify, not lyrics.",
					showCancelButton: true,
					confirmButtonText: "Download",
					cancelButtonText: "Cancel",
					customClass: {
						confirmButton: "btn btn-primary btn-lg ms-2",
						cancelButton: "btn btn-danger btn-lg"
					},
					buttonsStyling: false,
					inputOptions: {
						spotify: "Spotify",
						spotdl: "SpotDL",
						youtube: "YouTube",
						deezer: "Deezer"
					},
					input: "select",
					inputPlaceholder: "Select download source",
					inputValidator: (value) => {
						return new Promise((resolve) => {
							if (!value) resolve("Please select download source to continue");
							else resolve();
						});
					},
					reverseButtons: true,
					showLoaderOnConfirm: true,
					preConfirm: async (value) => {
						try {
							const response = await $.ajax({
								url: "/spotify/download",
								data: { url: $(this).data("href"), source: value }
								// xhrFields: {
								// 	responseType: "blob" // Fetch as binary data
								// }
							})
								.done(function (data) {
									return JSON.stringify(data);
								})
								.fail(function (xhr, st, err) {
									console.warn(err);
									throw new Error(
										st === "timeout"
											? "Connection timed out"
											: (xhr.responseJSON?.message ??
													"Server connection was lost")
									);
								});
							return response;
						} catch (error) {
							Swal.showValidationMessage(
								`Download failed: ${error.responseJSON?.message ?? "Server connection was lost or timed out"}`
							);
						}
					},
					allowOutsideClick: () => !Swal.isLoading(),
					allowEscapeKey: () => !Swal.isLoading(),
					allowEnterKey: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						musicDL(
							result.value.directUrl,
							`${$(this).data("artist")} - ${$(this).data("title")}`
						);
					}
				});
			});
if (lyricsModal) {
	lyricsModal.addEventListener("show.coreui.modal", function (e) {
		const btn = e.relatedTarget;
		const songName = btn.getAttribute("data-coreui-track"),
			artistName = btn.getAttribute("data-coreui-artist"),
			songID = btn.getAttribute("data-coreui-id");
		fileName = `${artistName} - ${songName}`;
		meta = `\n[ar:${artistName}]\n[ti:${songName}]\n[by:Musixmatch Spotify]\n`;
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);
		$.ajax({
			url: `/spotify/${songID}`,
			beforeSend: function () {
				$("#lyrics-content").text("");
				$("#song-release-date").text("");
				$("#song-last-update").text("");
				$("#song-copyright").text("");
				$("#lyric-type").text("");
				$("#musixmatch-btn").attr("href", "#");
				$(".placeholder-glow").removeClass("d-none");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
			},
			success: function (data) {
				plainContents = `${fileName}\n\n${data.plain}`;
				if (data.synced === "" || data.synced === null) {
					syncedDL.classList.add("disabled");
					$("#lyric-type").text("Plain");
					syncedContents = null;
				} else {
					syncedDL.classList.remove("disabled");
					$("#lyric-type").text("Synced");
					syncedContents = `[id: ${data.id}]${meta}${data.synced}`;
				}
				if (data.richsync === true || data.richsync === 1) {
					track_id = data.track_id;
					richsyncDL.classList.remove("disabled");
					$("#lyric-type").text("Richsync");
				} else {
					track_id = null;
					richsyncDL.classList.add("disabled");
				}
				$("#lyrics-content").text(data.plain);
				$("#song-release-date").text(data.release);
				$("#song-last-update").text(data.updated);
				$("#song-copyright").text(data.copyright);
				$("#musixmatch-btn").attr("href", data.share);
			},
			error: function (xhr, st, err) {
				console.warn(err);
				toast.fire({
					icon: "error",
					text:
						st === "timeout"
							? "Connection timed out"
							: (xhr.responseJSON?.message ?? "Server connection was lost")
				});
				$("#modalMX").modal("hide");
			}
		});
	});
} else console.warn("No lyric preview modal found");
plainDL.onclick = function (e) {
	e.preventDefault();
	blobDL(plainContents, `${fileName}.txt`);
};
syncedDL.onclick = function (e) {
	e.preventDefault();
	blobDL(syncedContents, `${fileName}.lrc`);
};
richsyncDL.onclick = function (e) {
	e.preventDefault();
	swalConfirm
		.fire({
			title: "Download Richsync lyric?",
			text: "Musixmatch richsync lyric is a word-by-word version of synced lyric and not all players are supported.",
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
							console.warn(`${st}: ${err}`);
							throw new Error(
								xhr.responseJSON?.message ?? "Server connection was lost"
							);
						}
					});
					return response;
				} catch (e) {
					Swal.showValidationMessage(
						`Download failed: ${e.responseJSON?.message ?? "Server connection was lost"}`
					);
				}
			}
		})
		.then((result) => {
			if (result.isConfirmed) {
				blobDL(
					`[id:${result.value.id}]${meta}${result.value.content}`,
					`${fileName}.lrc`
				);
			}
		});
};