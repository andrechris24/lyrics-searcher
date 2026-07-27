/* global blobDL, toast, Swal, swalConfirm, basicForm */
let plainContents, syncedContents, fileName, track_id, meta;
const plainDL = document.getElementById("download-link-plain"),
	syncedDL = document.getElementById("download-link-synced"),
	richsyncDL = document.getElementById("download-link-richsync"),
	lyricsModal = document.getElementById("modalMX");
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});
$(basicForm).submit(function (event) {
	event.preventDefault();
	const formData = $(this).serialize();
	$("#spotify-container").html("");
	$("form :input").prop("disabled", true);
	$("#spotify-loader").removeClass("d-none");
	$.ajax({ url: "/spotify/results", data: formData })
		.done(function (r) {
			$("#spotify-container").html(r.html);
		})
		.fail(function (xhr, st, err) {
			console.warn(err);
			if (
				xhr.status === 422 &&
				typeof xhr.responseJSON.errors.query !== "undefined"
			)
				$("#basic-search-query").addClass("is-invalid");
			toast.fire({
				icon: "error",
				text:
					st === "timeout"
						? "Connection timed out"
						: (xhr.responseJSON?.message ?? "Server connection was lost")
			});
		})
		.always(function () {
			$("#spotify-loader").addClass("d-none");
		});
});
if (lyricsModal) {
	lyricsModal.addEventListener("show.coreui.modal", function (e) {
		const btn = e.relatedTarget;
		const songName = btn.getAttribute("data-coreui-title"),
			artistName = btn.getAttribute("data-coreui-artist"),
			albumName = btn.getAttribute("data-coreui-album"),
			duration = btn.getAttribute("data-coreui-duration"),
			songID = btn.getAttribute("data-coreui-id");
		fileName = `${artistName} - ${songName}`;
		meta = `\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n[length: ${duration}]\n[by: Musixmatch (Spotify)]\n`;
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);
		$("#song-album").text(albumName);
		$("#song-duration").text(duration);
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
					`[id: ${result.value.id}]${meta}${result.value.content}`,
					`${fileName}.lrc`
				);
			}
		});
};
