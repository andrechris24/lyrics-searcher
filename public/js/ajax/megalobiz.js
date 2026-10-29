/* global luxon, toast, blobDL, basicForm, advancedAltForm */
let lrcContents, fileName;
const lyricsModal = document.getElementById("modalLyrics");
if (lyricsModal) {
	lyricsModal.addEventListener("show.coreui.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-coreui-* attributes
		const songName = button.getAttribute("data-coreui-title"),
			artistName = button.getAttribute("data-coreui-artist"),
			lrcTiming = button.getAttribute("data-coreui-type"),
			songID = button.getAttribute("data-coreui-id"),
			duration = button.getAttribute("data-coreui-duration");

		// Update the modal's content
		$("#song-lyric-type").text(lrcTiming);
		$("#song-duration").text(duration);
		$("#song-title").text(songName);
		$("#song-artist").text(artistName ?? '-');

		// Set file name and contents on save
		fileName = (artistName === '' || artistName === null ? artistName + ' - ' : '') + songName;
		$.ajax({
			url: `/megalobiz/${songID}`,
			beforeSend: function () {
				$(".placeholder-glow").removeClass("d-none");
				$("#lyric-content").text("");
				$("#lrc-owner").text("");
				$("#lrc-update").text("");
				$("#save-btn").prop("disabled", true);
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
			},
			success: function (data) {
				try {
					lrcContents = (data.synced_by === '' || data.synced_by === null ? '[by:MegaloBiz]\n' : '') + data.lrc;
					$("#save-btn").prop("disabled", false);
					$("#lyric-content").text(data.lrc);
					$("#lrc-owner").text(data.synced_by ?? 'Guest');
					$("#lrc-update").text(luxon.DateTime.fromISO(data.updated_at).toFormat('cccc, d LLLL yyyy'));
				} catch (e) {
					console.error(e);
					toast.fire({
						icon: "error",
						text: "Script error detected while fetching lyric. Please contact site oowner."
					});
				}
			},
			error: function (xhr, st, err) {
				console.warn(`${st}: ${err}`);
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
} else console.warn("No lyrics modal found");
$(document).on("click", "#save-btn", function () {
	let ext;
	if (!lrcContents.match(/\[(\d+):(\d+).(\d+)\]/)) ext = 'txt';
	else ext = 'lrc';
	blobDL(lrcContents, `${fileName}.${ext}`);
});
$(basicForm).submit(function (event) {
	event.preventDefault();
	sendAjax("/megalobiz/results", $(basicForm).serialize());
});
$(advancedAltForm).submit(function (event) {
	event.preventDefault();
	sendAjax("/megalobiz/advanced/results", $(advancedAltForm).serialize());
});
function sendAjax(url, data) {
	$("#megalobiz-container").html("");
	$("form :input").prop("disabled", true);
	$("#megalobiz-loader").removeClass("d-none");
	$.ajax({
		url: url,
		data: data
	})
		.done(function (r) {
			$("#megalobiz-container").html(r.html);
		})
		.fail(function (xhr, st, err) {
			console.warn(`${st}: ${err}`);
			try {
				if (xhr.status === 422) {
					if (typeof xhr.responseJSON.errors.title !== "undefined")
						$("#track-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.artist !== "undefined")
						$("#artist-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.query !== "undefined")
						$("#basic-search-query").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.minutes !== "undefined")
						$("#search-minutes").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.seconds !== "undefined")
						$("#search-seconds").addClass("is-invalid");
				}
				toast.fire({
					icon: "error",
					text:
						st === "timeout"
							? "Connection timed out"
							: (xhr.responseJSON?.message ?? "Server connection was lost")
				});
			} catch (e) {
				console.error(e);
				toast.fire({
					icon: "error",
					text: "An error occurred while marking input errors. Please report to developer."
				});
			}
		})
		.always(function () {
			$("#megalobiz-loader").addClass("d-none");
		});
}
