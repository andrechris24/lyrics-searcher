/* global toast */
let syncedLyricContents,
	sylLyricContent,
	plainLyricContent,
	ttmlContent,
	fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	plainLyricDL = document.getElementById("dl-plain"),
	syncedLyricDL = document.getElementById("dl-synced"),
	sylLyricDL = document.getElementById("dl-syllyric"),
	ttmlDL = document.getElementById("dl-ttml"),
	previewModal = document.getElementById("modalPreviewSong"),
	player = $("#preview-player");
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-title"),
			artistName = button.getAttribute("data-bs-artist"),
			albumName = button.getAttribute("data-bs-album"),
			songID = button.getAttribute("data-bs-id"),
			duration = button.getAttribute("data-bs-duration");
		const metaLyric =
			`\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n` +
			`[by: Apple Music]\n[length: ${duration}]\n`;

		// Update the modal's content
		$("#song-album").text(albumName);
		$("#song-duration").text(duration);
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);

		// Set file name and contents on save
		fileName = `${artistName} - ${songName}`;
		$.ajax({
			url: `/apple/${songID}`,
			beforeSend: function () {
				$(".placeholder-glow").removeClass("d-none");
				$("#lyrics-content").text("");
				$("#song-writers").text("");
				$("#song-lyric-type").text("");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
			},
			success: function (data) {
				if (data.synced !== null) {
					$("#dl-synced").removeClass("disabled");
					syncedLyricContents = `[id: ${data.id}]${metaLyric}[lr: ${data.writers}]\n${data.synced}`;
				} else {
					$("#dl-synced").addClass("disabled");
					syncedLyricContents = "";
				}
				if (data.syllable !== null) {
					$("#dl-syllyric").removeClass("disabled");
					sylLyricContent = `[id: ${data.id}]${metaLyric}[lr: ${data.writers}]\n${data.syllable}`;
				} else {
					sylLyricContent = "";
					$("#dl-syllyric").addClass("disabled");
				}
				if (data.ttml !== null) {
					$("#dl-ttml").removeClass("disabled");
					ttmlContent = data.ttml;
				} else {
					$("#dl-ttml").addClass("disabled");
					ttmlContent = "";
				}
				plainLyricContent = `${fileName}\n\n${data.plain}`;
				$("#song-writers").text(data.writers);
				$("#song-lyric-type").text(data.type);
				$("#lyrics-content").text(data.plain);
			},
			error: function (xhr, st, err) {
				console.warn(err);
				toast.fire({
					icon: "error",
					text:
						st === "timeout"
							? "Connection timed out"
							: (xhr.responseJSON?.message ?? err ?? st)
				});
				$("#modalLyrics").modal("hide");
			}
		});
	});
}
if (previewModal) {
	previewModal.addEventListener("show.bs.modal", function (e) {
		const attr = e.relatedTarget;
		const songName = attr.getAttribute("data-bs-title"),
			artistName = attr.getAttribute("data-bs-artist"),
			albumName = attr.getAttribute("data-bs-album"),
			songLink = attr.getAttribute("data-bs-link"),
			duration = attr.getAttribute("data-bs-duration");
		$("#preview-album").text(albumName);
		$("#preview-duration").text(duration);
		$("#preview-title").text(songName);
		$("#preview-artist").text(artistName);
		$("#preview-song").attr("src", songLink);
		player[0].pause();
		player[0].load();
		player[0].oncanplaythrough = player[0].play();
	});
	previewModal.addEventListener("hidden.bs.modal", function () {
		player[0].pause();
	});
}
syncedLyricDL.onclick = function () {
	syncedLyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(syncedLyricContents)}`;
	syncedLyricDL.download = `${fileName}.lrc`;
};
sylLyricDL.onclick = function () {
	sylLyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(sylLyricContent)}`;
	sylLyricDL.download = `${fileName}.lrc`;
};
plainLyricDL.onclick = function () {
	plainLyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(plainLyricContent)}`;
	plainLyricDL.download = `${fileName}.txt`;
};
ttmlDL.onclick = function () {
	ttmlDL.href = `data:application/ttml+xml;charset=utf-8,${encodeURIComponent(ttmlContent)}`;
	ttmlDL.download = `${fileName}.ttml`;
};
