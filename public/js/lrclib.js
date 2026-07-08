/* global Swal, blobDL, swalConfirm */
let plainContents, syncedContents, wbwContents, fileName;
const lyricsModal = document.getElementById("modalLRCLib"),
	plainDL = document.getElementById("download-link-lrclib-plain"),
	syncedDL = document.getElementById("download-link-lrclib-synced"),
	wbwDL = document.getElementById("download-link-lrclib-wbw");
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-title"),
			artistName = button.getAttribute("data-bs-artist"),
			albumName = button.getAttribute("data-bs-album"),
			syncedLyrics = button.getAttribute("data-bs-synced"),
			plainLyrics = button.getAttribute("data-bs-plain"),
			wbwLyrics = button.getAttribute("data-bs-wordbyword"),
			duration = button.getAttribute("data-bs-duration"),
			lyricID = button.getAttribute("data-bs-id");

		// Update the modal's content
		const songArtist = document.getElementById("lrclib-song-artist"),
			songTitle = document.getElementById("lrclib-song-title"),
			songAlbum = document.getElementById("lrclib-song-album"),
			songDuration = document.getElementById("lrclib-song-duration"),
			plainContainer = document.getElementById("lrclib-content");
		plainContainer.textContent = plainLyrics;
		songArtist.textContent = artistName;
		songTitle.textContent = songName;
		songAlbum.textContent = albumName;
		songDuration.textContent = duration;

		// Set file name and contents on save
		fileName = `${songArtist.textContent} - ${songTitle.textContent}`;
		plainContents = `${fileName}\n\n${plainLyrics}`;
		if (syncedLyrics === "" || syncedLyrics === null) {
			syncedDL.classList.add("disabled");
			syncedContents = null;
			$("#lrclib-lyric-type").text("Plain");
		} else {
			syncedDL.classList.remove("disabled");
			syncedContents =
				`[id: ${lyricID}]\n[ar: ${artistName}]\n[ti: ${songName}]\n` +
				`[al: ${albumName}]\n[by: LRCLib]\n` +
				`[length: ${songDuration.textContent}]\n${syncedLyrics}`;
			$("#lrclib-lyric-type").text("Synced");
		}
		if (wbwLyrics === null || wbwLyrics === "") {
			wbwDL.classList.add("disabled");
			wbwContents = null;
		} else {
			wbwDL.classList.remove("disabled");
			wbwContents = wbwLyrics;
			$("#lrclib-lyric-type").text("Word-by-word");
		}
	});
}
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});
plainDL.onclick = function (e) {
	e.preventDefault();
	blobDL(plainContents, `${fileName}.txt`);
};
syncedDL.onclick = function (e) {
	e.preventDefault();
	blobDL(syncedContents, `${fileName}.lrc`);
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
						data: { content: wbwContents },
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
			if (result.isConfirmed) blobDL(result.value.lrc, `${fileName}.lrc`);
			else if (result.isDenied) blobDL(wbwContents, `${fileName}.yaml`);
			else console.warn("Download aborted");
		});
};
