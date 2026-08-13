/* global toast, blobDL, basicForm, coreui */
let syncedLyricContents, sylLyricContent, plainLyricContent, fileName;
const lyricsModal = document.getElementById("modalLyrics"),
	plainLyricDL = document.getElementById("dl-plain"),
	syncedLyricDL = document.getElementById("dl-synced"),
	sylLyricDL = document.getElementById("dl-syllyric"),
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
			url: `/deezer/${songID}`,
			beforeSend: function () {
				$(".placeholder-glow").removeClass("d-none");
				$("#lyrics-content").text("");
				$("#song-writers").text("");
				$("#song-copyright").text("");
				$("#song-license").text("");
				$("#song-lyric-type").text("");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
			},
			success: function (data) {
				if (data.synced !== null && data.synced !== "") {
					const metaLyric =
						`[id: ${data.id}]\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n` +
						`[by: Deezer]\n[length: ${duration}]\n[lr: ${data.writer}]\n`;
					if (data.synced.match(/<(\d+):(\d+).(\d+)>/g)) {
						$("#dl-syllyric").removeClass("disabled");
						$("#song-lyric-type").text("Syllable");
						sylLyricContent = `${metaLyric}${data.synced}`;
						syncedLyricContents = `${metaLyric}${data.synced.replace(/<(\d+):(\d+).(\d+)>/g, "")}`;
					} else {
						$("#dl-syllyric").addClass("disabled");
						$("#song-lyric-type").text("Synced");
						sylLyricContent = "";
						syncedLyricContents = `${metaLyric}${data.synced}`;
					}
				} else {
					$("#dl-syllyric").addClass("disabled");
					$("#dl-synced").addClass("disabled");
					$("#song-lyric-type").text("Plain");
					sylLyricContent = syncedLyricContents = "";
				}
				plainLyricContent = `${fileName}\n\n${data.plain}`;
				$("#song-writers").text(data.writer);
				$("#song-copyright").text(data.copyright);
				$("#song-license").text(data.license);
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
	$("#deezer-container").html("");
	$("#deezer-loader").removeClass("d-none");
	sendAjax($(this).serialize());
});
function sendAjax(data) {
	$("form :input").prop("disabled", true);
	$.ajax({
		url: "/deezer/results",
		data: data
	})
		.done(function (r) {
			$("#deezer-container").html(r.html);
			const openTooltip = document.querySelectorAll(
				'a[data-coreui-toggle="tooltip"].btn-success'
			);
			// eslint-disable-next-line no-unused-vars
			const openList = [...openTooltip].map(
				(tooltipTriggerEl) => new coreui.Tooltip(tooltipTriggerEl)
			);
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
			$("#deezer-loader").addClass("d-none");
			$("#deezer-container").LoadingOverlay("hide");
		});
}
// eslint-disable-next-line no-unused-vars
function navigate(query, offset) {
	$("#deezer-container").LoadingOverlay("show");
	sendAjax({ query: query, offset: offset });
	$("html, body").animate(
		{ scrollTop: $("#deezer-container").offset().top },
		200
	);
}
syncedLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(syncedLyricContents, `${fileName}.lrc`);
};
sylLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(sylLyricContent, `${fileName}.lrc`);
};
plainLyricDL.onclick = function (e) {
	e.preventDefault();
	blobDL(plainLyricContent, `${fileName}.txt`);
};
