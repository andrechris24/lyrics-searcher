/* global blobDL, toast, basicForm, musicDL, coreui, Swal */
let syncedLyricContents, plainLyricContent, fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	plainLyricDL = document.getElementById("dl-plain"),
	syncedLyricDL = document.getElementById("dl-synced");
// sylLyricDL = document.getElementById("dl-syllyric"),
if (lyricsModal) {
	lyricsModal.addEventListener("show.coreui.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-coreui-* attributes
		const songName = button.getAttribute("data-coreui-track"),
			artistName = button.getAttribute("data-coreui-artist"),
			songID = button.getAttribute("data-coreui-id"),
			duration = button.getAttribute("data-coreui-duration");

		// Update the modal's content
		$("#song-duration").text(duration);
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);

		// Set file name and contents on save
		fileName = `${artistName} - ${songName}`;
		$.ajax({
			url: `/amazon/${songID}`,
			beforeSend: function () {
				$(".placeholder-glow").removeClass("d-none");
				$("#lyrics-content").text("");
				// $("#song-writers").text("");
				// $("#song-copyright").text("");
				// $("#song-license").text("");
				$("#song-lyric-type").text("");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
			},
			success: function (data) {
				const metaLyric = `[id: ${data.id}]\n[ar: ${artistName}]\n[ti: ${songName}]\n[by: Amazon Music]\n`;
				if (data.synced !== null && data.synced !== "") {
					$("#song-lyric-type").text("Synced");
					$("#dl-synced").removeClass("disabled");
					syncedLyricContents = `${metaLyric}${data.synced}`;
				} else {
					// $("#dl-syllyric").addClass("disabled");
					$("#dl-synced").addClass("disabled");
					$("#song-lyric-type").text("Plain");
					syncedLyricContents = "";
				}
				// if (data.wbw !== null && data.wbw !== "") {
				// 	$("#dl-syllyric").removeClass("disabled");
				// 	$("#song-lyric-type").text("Word-by-Word");
				// 	sylLyricContent = `${metaLyric}${data.wbw}`;
				// } else {
				// 	$("#dl-syllyric").addClass("disabled");
				// 	sylLyricContent = "";
				// }
				plainLyricContent = `${fileName}\n\n${data.plain}`;
				if (Array.isArray(data.syllable) && data.syllable.length > 0){
					toast.fire({
						icon: "info",
						text: "This song may contain word-by-word or syllable lyric. Please contact site owner to confirm."
					});
				}
				$("#lyrics-content").text(data.plain);
			},
			error: function (xhr, st, err) {
				console.warn(err);
				toast.fire({
					icon: "error",
					text:
						st === "timeout"
							? "Connection timed out"
							: (xhr.responseJSON?.message ?? "Server connection was lost")
				});
				$("#modalLyrics").modal("hide");
			}
		});
	});
} else console.warn("No lyric modal exist");
$(basicForm).submit(function (event) {
	event.preventDefault();
	$("#amazon-container").html("");
	$("#amazon-loader").removeClass("d-none");
	sendAjax($(this).serialize());
});
function sendAjax(data) {
	$("form :input").prop("disabled", true);
	$.ajax({
		url: "/amazon/results",
		data: data
	})
		.done(function (r) {
			$("#amazon-container").html(r.html);
			const openTooltip = document.querySelectorAll(
				'[data-coreui-toggle="tooltip"]'
			);
			// eslint-disable-next-line no-unused-vars
			const openList = [...openTooltip].map(
				(tooltipTriggerEl) => new coreui.Tooltip(tooltipTriggerEl)
			);
			$(".download-btn").on("click", function () {
				$.LoadingOverlay("show");
				const href = $(this).data("href"),
					artist = $(this).data("artist"),
					title = $(this).data("title");
				$.ajax({
					url: `/amazon/download`,
					data: { url: href },
					// xhrFields: {
					// 	responseType: "blob" // Fetch as binary data
					// },
					complete: function () {
						$.LoadingOverlay("hide");
					},
					success: function (r) {
						let file;
						if (typeof r.data !== "undefined")
							file = `${r.data.artist} - ${r.data.name}`;
						else file = `${artist} - ${title}`;
						if(r.url.includes('.flac')){
							Swal.fire({
								icon: 'warning',
								titleText: 'Important Note',
								text:'If none of your players can open this file, open it with file archiver like 7Zip or WinRAR.'
							});
						}
						musicDL(r.directUrl, file);
					},
					error: function (xhr, st, err) {
						$.LoadingOverlay("hide");
						console.warn(err);
						toast.fire({
							icon: "error",
							text:
								st === "timeout"
									? "Connection timed out"
									: (xhr.responseJSON?.message ?? err ?? st)
						});
					}
				});
			});
		})
		.fail(function (xhr, st, err) {
			console.warn(err);
			if (
				xhr.status === 422 &&
				typeof xhr.responseJSON.errors.query !== "undefined"
			)
				$("#basic-search-query").addClass("is-invalid");
			toast.fire({
				icon: "error",
				text:
					st === "timeout"
						? "Connection timed out"
						: (xhr.responseJSON?.message ?? "Server connection was lost")
			});
		})
		.always(function () {
			$("#amazon-loader").addClass("d-none");
			$("#amazon-container").LoadingOverlay("hide");
		});
}
syncedLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(syncedLyricContents, `${fileName}.lrc`);
};
// sylLyricDL.onclick = function (e) {
// 	e.preventDefault();
// 	blobDL(sylLyricContent, `${fileName}.lrc`);
// };
plainLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(plainLyricContent, `${fileName}.txt`);
};
