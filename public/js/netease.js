let lyricContents, fileName, message;
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
		fileName = `${artistName} - ${songName}.lrc`;
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
				if (typeof data.klyric !== "undefined") {
					console.log(data.klyric.lyric);
					if (data.klyric.lyric !== "") {
						toast.fire({
							icon: "info",
							text: "This song contains word-by word lyrics.",
						});
					}
				}
				lyricContents =
					`[ar: ${artistName}]\n` +
					`[ti: ${songName}]\n` +
					`[al: ${albumName}]\n` +
					`[by: NetEase]\n` +
					`[length: ${duration}]\n${data.lrc.lyric}`;
				$("#lyrics-content").html(data.lrc.lyric.replace(/\n/g, "<br/>"));
			},
			error: function (xhr, st) {
				if (st === "timeout") message = "Connection timed out";
				else message = xhr.responseJSON.message ?? st;
				toast.fire({ icon: "error", text: message });
				$("#modalLyrics").modal('hide');
			}
		});
	});
}
// function parseKLyric(lyricText){
// 		let enhancedlyricText = "";
// 		let matches;
// 		let metaRegex = /^\[(\S+):(\S+)\]$/;
// 		let timestampsRegex = /^\[(\d+),(\d+)\]/;
// 		let timestamps2Regex = /\((\d+),(\d+)\)([^\(]*)/g;
// 		let lines = lyricText.split(/[\r\n]/);
// 		for (const line of lines) {
// 				if (matches = metaRegex.exec(line)) { // meta info
// 						enhancedlyricText += `${matches[0]}\r\n`;
// 				} else if (matches = timestampsRegex.exec(line)) {
// 						let startTime = parseInt(matches[1]);
// 						let duration = parseInt(matches[2]);
// 						let lyricLine = "[" + formatTime(startTime) + "]";
// 						// parse sub-timestamps
// 						let subMatches;
// 						let subStartTime = startTime;
// 						while (subMatches = timestamps2Regex.exec(line)) {
// 								let subDuration = parseInt(subMatches[2]);
// 								let subWord = subMatches[3];
// 								lyricLine += "<" + formatTime(subStartTime) + `>${subWord}`;
// 								subStartTime += subDuration;
// 						}
// 						lyricLine += "<" + formatTime(startTime + duration) + ">";
// 						enhancedlyricText += `${lyricLine}\r\n`;
// 				}
// 		}
// 		return enhancedlyricText;
// }
lyricDL.onclick = function () {
	lyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(lyricContents)}`;
	lyricDL.download = `${fileName}.lrc`;
};
