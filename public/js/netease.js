let lyricContents, fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	lyricDL = document.querySelector("#save-btn");
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-title"),
			artistName = button.getAttribute("data-bs-artist"),
			albumName = button.getAttribute("data-bs-album"),
			songID = button.getAttribute("data-bs-id"),
			duration = button.getAttribute("data-bs-duration");
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
			url: `/netease/${songID}`,
			beforeSend: function () {
				$("#save-btn").prop("disabled", true);
				$(".placeholder-glow").removeClass("d-none");
				$("#lyrics-content").html("");
				$.LoadingOverlay("show");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
				$.LoadingOverlay("hide");
			},
			success: function (data) {
				$("#save-btn").prop("disabled", false);
				if (typeof data.needDesc !== "undefined" && data.needDesc === true) {
					toast.fire({
						icon: "warning",
						text: "Lyric might be incomplete or does not contain any lyric.",
					});
				}
				lyricContents =
					`[ar: ${artistName}]\n` +
					`[ti: ${songName}]\n` +
					`[al: ${albumName}]\n` +
					`[by: NetEase]\n` +
					`[length: ${duration}]\n${data.lrc.lyric}`;
				$("#lyrics-content").html(data.lrc.lyric.replace(/\n/g, "<br/>"));
				// if(typeof data.klyric !== 'undefined')
				// 	console.log(data.klyric);
			},
			error: function (xhr, st) {
				toast.fire({ icon: "error", text: xhr.responseJSON.message ?? st });
			},
		});
	});
}
lyricDL.onclick = function () {
	lyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(lyricContents)}`;
	lyricDL.download = `${fileName}.lrc`;
};
