let plainContents, syncedContents, fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	plainTab = document.querySelector(
		'#js-tabs-2 button[data-bs-target="#nav-plain"]',
	),
	syncedTab = document.querySelector(
		'#js-tabs-2 button[data-bs-target="#nav-synced"]',
	),
	plainDL = document.querySelector("#download-link-plain"),
	syncedDL = document.querySelector("#download-link-synced");
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-title"),
			artistName = button.getAttribute("data-bs-artist"),
			albumName = button.getAttribute("data-bs-album"),
			syncedLyrics = button.getAttribute("data-bs-synced"),
			plainLyrics = button.getAttribute("data-bs-plain"),
			duration = button.getAttribute("data-bs-duration");
		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback

		// Update the modal's content
		const songArtist = lyricsModal.querySelector("#song-artist"),
			songTitle = lyricsModal.querySelector("#song-title"),
			songAlbum = lyricsModal.querySelector("#song-album"),
			songDuration = lyricsModal.querySelector("#song-duration"),
			plainContainer = lyricsModal.querySelector("#plain-lyrics-content"),
			syncedContainer = lyricsModal.querySelector("#synced-lyrics-content");
		plainContainer.textContent = plainLyrics;
		syncedContainer.textContent = syncedLyrics;
		songArtist.textContent = artistName;
		songTitle.textContent = songName;
		songAlbum.textContent = albumName;
		songDuration.textContent = duration;

		// Set file name and contents on save
		fileName = `${songArtist.textContent} - ${songTitle.textContent}`;
		plainContents = plainContainer.textContent;
		if (syncedLyrics === "") {
			bootstrap.Tab.getInstance(plainTab).show();
			syncedTab.disabled = true;
			syncedDL.classList.add("disabled");
			syncedContents = null;
		} else {
			syncedTab.disabled = false;
			syncedDL.classList.remove("disabled");
			syncedContents =
				`[ar: ${artistName}]\n` +
				`[ti: ${songName}]\n` +
				`[al: ${albumName}]\n` +
				`[by: LRCLib]\n` +
				`[length: ${songDuration.textContent}]\n${syncedContainer.textContent}`;
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
