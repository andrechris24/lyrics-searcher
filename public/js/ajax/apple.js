/* global toast, blobDL, basicForm */
let syncedLyricContents,
	sylLyricContent,
	plainLyricContent,
	multisylLyricContent,
	ttmlContent,
	fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	plainLyricDL = document.getElementById("dl-plain"),
	syncedLyricDL = document.getElementById("dl-synced"),
	sylLyricDL = document.getElementById("dl-syllyric"),
	multiSylLyricDL = document.getElementById("dl-multisyllyric"),
	ttmlDL = document.getElementById("dl-ttml"),
	previewModal = document.getElementById("modalPreviewSong"),
	player = $("#preview-player");
if (lyricsModal) {
	lyricsModal.addEventListener("show.coreui.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-coreui-* attributes
		const songName = button.getAttribute("data-coreui-title"),
			artistName = button.getAttribute("data-coreui-artist"),
			albumName = button.getAttribute("data-coreui-album"),
			songID = button.getAttribute("data-coreui-id"),
			duration = button.getAttribute("data-coreui-duration");

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
				const metaLyric =
					`[id: ${data.id}]\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n` +
					`[by: Apple Music]\n[length: ${duration}]\n[lr: ${data.writers}]\n`;
				if (data.synced !== null && data.synced !== "") {
					$("#dl-synced").removeClass("disabled");
					syncedLyricContents = `${metaLyric}${data.synced}`;
				} else {
					$("#dl-synced").addClass("disabled");
					syncedLyricContents = "";
				}
				if (data.syllable !== null && data.syllable !== "") {
					$("#dl-syllyric").removeClass("disabled");
					sylLyricContent = `${metaLyric}${data.syllable}\n[${duration}.000]`;
				} else {
					sylLyricContent = "";
					$("#dl-syllyric").addClass("disabled");
				}
				if (data.multisyl !== null && data.multisyl !== "") {
					$("#dl-multisyllyric").removeClass("disabled");
					multisylLyricContent = `${metaLyric}${data.multisyl}\n[${duration}.000]`;
				} else {
					multisylLyricContent = "";
					$("#dl-multisyllyric").addClass("disabled");
				}
				if (data.ttml !== null && data.ttml !== "") {
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
} else console.warn("No lyrics modal found");
if (previewModal) {
	previewModal.addEventListener("show.coreui.modal", function (e) {
		const attr = e.relatedTarget;
		const songName = attr.getAttribute("data-coreui-title"),
			artistName = attr.getAttribute("data-coreui-artist"),
			albumName = attr.getAttribute("data-coreui-album"),
			songLink = attr.getAttribute("data-coreui-link"),
			duration = attr.getAttribute("data-coreui-duration");
		$("#preview-album").text(albumName);
		$("#preview-duration").text(duration);
		$("#preview-title").text(songName);
		$("#preview-artist").text(artistName);
		$("#preview-song").attr("src", songLink);
		player[0].pause();
		player[0].load();
		player[0].oncanplaythrough = player[0].play();
	});
	previewModal.addEventListener("hidden.coreui.modal", function () {
		player[0].pause();
	});
} else console.warn("No song preview modal exist");
$(basicForm).submit(function (event) {
	event.preventDefault();
	const formData = $(this).serialize();
	$("form :input").prop("disabled", true);
	$("#apple-container").html("");
	$("#apple-loader").removeClass("d-none");
	$.ajax({
		url: "/apple/results",
		data: formData
	})
		.done(function (r) {
			$("#apple-container").html(r.html);
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
			$("#apple-loader").addClass("d-none");
		});
});
syncedLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(syncedLyricContents, `${fileName}.lrc`);
};
sylLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(sylLyricContent, `${fileName}.lrc`);
};
multiSylLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(multisylLyricContent, `${fileName}.lrc`);
};
plainLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(plainLyricContent, `${fileName}.txt`);
};
ttmlDL.onclick = function (e) {
	e.preventDefault();
	blobDL(ttmlContent, `${fileName}.ttml`, "application/ttml+xml");
};
