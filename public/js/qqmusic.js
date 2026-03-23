let lyricContents, fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	lyricDL = document.querySelector("#save-btn");
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-title"),
			artistName = button.getAttribute("data-bs-artist"),
			songID = button.getAttribute("data-bs-id");
		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback

		// Update the modal's content
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);

		// Set file name and contents on save
		fileName = `${artistName} - ${songName}`;
		$.ajax({
			method: "GET",
			url: `/qqmusic/${songID}`,
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
				lyricContents = data.lyric;
				$("#lyrics-content").html(lyricContents.replace(/\n/g, "<br/>"));
			},
			error: function (xhr, st) {
				toast.fire({icon: 'error',text: xhr.responseJSON.message ?? st});
			}
		});
	});
}
lyricDL.onclick = function () {
	lyricDL.href =
		`data:text/plain;charset=utf-8,${encodeURIComponent(lyricContents)}`;
	lyricDL.download = `${fileName}.lrc`;
};
