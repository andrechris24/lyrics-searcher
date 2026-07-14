/* global blobDL, toast, Swal, swalConfirm */
let plainContents, syncedContents, fileName, track_id, meta;
const plainDL = document.getElementById("download-link-plain"),
	syncedDL = document.getElementById("download-link-synced"),
	richsyncDL = document.getElementById("download-link-richsync"),
	lyricsModal = document.getElementById("modalMX");
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", function (e) {
		const btn = e.relatedTarget;
		const songName = btn.getAttribute("data-bs-title"),
			artistName = btn.getAttribute("data-bs-artist"),
			albumName = btn.getAttribute("data-bs-album"),
			duration = btn.getAttribute("data-bs-duration"),
			songID = btn.getAttribute("data-bs-id");
		fileName = `${artistName} - ${songName}`;
		meta = `\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n[length: ${duration}]\n[by: Musixmatch (Spotify)]\n`;
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);
		$("#song-album").text(albumName);
		$("#song-duration").text(duration);
		$.ajax({
			url: `/spotify/${songID}`,
			beforeSend: function () {
				$("#lyrics-content").text('');
				$("#song-release-date").text('');
				$("#song-last-update").text('');
				$("#song-copyright").text('');
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
							: (xhr.responseJSON?.message ?? err ?? st),
				});
			},
		});
	});
}
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
					`[id: ${result.value.id}]${meta}${result.value.content}`,
					`${fileName}.lrc`
				);
			}
		});
};
