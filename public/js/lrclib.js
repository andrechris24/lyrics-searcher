let plainContents, syncedContents, fileName;
const lyricsModal = document.getElementById("modalLRCLib"),
	plainDL = document.getElementById("download-link-lrclib-plain"),
	syncedDL = document.getElementById("download-link-lrclib-synced");
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
		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback

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

		if (wbwLyrics===null || wbwLyrics==='') $("#lrclib-wbw").addClass('d-none');
		else $("#lrclib-wbw").removeClass('d-none');

		// Set file name and contents on save
		fileName = `${songArtist.textContent} - ${songTitle.textContent}`;
		plainContents = `${fileName}\n\n${plainLyrics}`;
		if (syncedLyrics === "") {
			syncedDL.classList.add("disabled");
			syncedContents = null;
		} else {
			syncedDL.classList.remove("disabled");
			syncedContents =
				`[id: ${lyricID}]\n[ar: ${artistName}]\n[ti: ${songName}]\n` +
				`[al: ${albumName}]\n[by: LRCLib]\n` +
				`[length: ${songDuration.textContent}]\n${syncedLyrics}`;
		}
	});
}
plainDL.onclick = function () {
	plainDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(plainContents)}`;
	plainDL.download = `${fileName}.txt`;
};
syncedDL.onclick = function () {
	syncedDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(syncedContents)}`;
	syncedDL.download = `${fileName}.lrc`;
};
