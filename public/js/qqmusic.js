let fileName, message;
// const lyricsModal = document.getElementById("modalLyrics"),
// 	lyricDL = document.querySelector("#save-btn");
// if (lyricsModal) {
// 	lyricsModal.addEventListener("show.bs.modal", (event) => {
// 		const button = event.relatedTarget;

// 		// Extract info from data-bs-* attributes
// 		const songName = button.getAttribute("data-bs-title"),
// 			artistName = button.getAttribute("data-bs-artist"),
// 			songID = button.getAttribute("data-bs-id");
// 		// If necessary, you could initiate an Ajax request here
// 		// and then do the updating in a callback

// 		// Update the modal's content
// 		$("#song-title").text(songName);
// 		$("#song-artist").text(artistName);

// 		// Set file name and contents on save
// 		fileName = `${artistName} - ${songName}`;
// 		$.ajax({
// 			url: `/qqmusic/${songID}`,
// 			beforeSend: function () {
// 				$("#save-btn").prop("disabled", true);
// 				$(".placeholder-glow").removeClass("d-none");
// 				$("#lyrics-content").html("");
// 				$.LoadingOverlay("show");
// 			},
// 			complete: function () {
// 				$(".placeholder-glow").addClass("d-none");
// 				$.LoadingOverlay("hide");
// 			},
// 			success: function (data) {
// 				lyricContents = data.lyric;
// 				$("#save-btn").prop("disabled", false);
// 				$("#lyrics-content").html(lyricContents.replace(/\n/g, "<br/>"));
// 			},
// 			error: function (xhr, st) {
// 				if(st === 'timeout') message = "Connection timed out";
// 				else message = xhr.responseJSON.message ?? st;
// 				toast.fire({ icon: "error", text: message });
// 			},
// 		});
// 	});
// }
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}.lrc`;
	$.ajax({
		url: `/qqmusic/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			blobDL(data.lyric, fileName);
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		},
	});
});
// lyricDL.onclick = function () {
// 	lyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(lyricContents)}`;
// 	lyricDL.download = `${fileName}.lrc`;
// };
