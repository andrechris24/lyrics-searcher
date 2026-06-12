/* global toast */
let syncedLyricContents, sylLyricContent, plainLyricContent, fileName, message;
const lyricsModal = document.getElementById("modalLyrics"),
	plainLyricDL = document.getElementById("dl-plain"),
	syncedLyricDL = document.getElementById("dl-synced"),
	sylLyricDL = document.getElementById("dl-syllyric");
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
			`[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n` +
			`[by: Deezer]\n[length: ${duration}]\n`;
		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback

		// Update the modal's content
		$("#song-album").text(albumName);
		$("#song-duration").text(duration);
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);

		// Set file name and contents on save
		fileName = `${artistName} - ${songName}`;
		$.ajax({
			url: `/deezer/${songID}`,
			beforeSend: function () {
				$(".placeholder-glow").removeClass("d-none");
				$("#lyrics-content").html("");
				$.LoadingOverlay("show");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
				$.LoadingOverlay("hide");
			},
			success: function (data) {
				if (data.synced !== null) {
					if (data.synced.match(/<(\d+):(\d+).(\d+)>/g)) {
						$("#dl-syllyric").removeClass("disabled");
						sylLyricContent = `[id: ${data.id}]${metaLyric}[lr: ${data.writer}]\n${data.synced}`;
						syncedLyricContents = "";
					} else {
						$("#dl-syllyric").addClass("disabled");
						sylLyricContent = "";
					}
				} else {
					$("#dl-syllyric").addClass("disabled");
					sylLyricContent = "";
				}
				plainLyricContent = `${fileName}\n\n${data.plain}`;
				$("#lyrics-content").html(data.plain);
			},
			error: function (xhr, st, err) {
				console.warn(err);
				if (st === "timeout") message = "Connection timed out";
				else message = xhr.responseJSON?.message ?? err ?? st;
				toast.fire({ icon: "error", text: message });
				$("#modalLyrics").modal("hide");
			},
		});
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
