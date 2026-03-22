const lyricsModal = document.getElementById("modalMX"),
	plainDL = document.querySelector("#download-link-plain"),
	richsyncDL = document.querySelector("#download-link-richsync"),
	syncedDL = document.querySelector("#download-link-synced");
let plainContents, syncedContents, richsyncContents, songID, fileName;
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		$("#instrumental-message").addClass("d-none");
		$("#no-lyrics-message").addClass("d-none");
		$("#error-alert").addClass("d-none");
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-track"),
			artistName = button.getAttribute("data-bs-artist"),
			albumName = button.getAttribute("data-bs-album"),
			duration = button.getAttribute("data-bs-duration"),
			plain = button.getAttribute("data-bs-plain"),
			richsync = button.getAttribute("data-bs-richsync"),
			synced = button.getAttribute("data-bs-synced"),
			explicit = button.getAttribute("data-bs-explicit"),
			albumArt = button.getAttribute("data-bs-art"),
			updated = button.getAttribute("data-bs-update");

		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback
		songID = button.getAttribute("data-bs-id");
		syncedDL.href = richsyncDL.href= "#";
		$("#lyrics-content").text("");
		$("#song-copyright").text("");

		// Update the modal's content
		if (explicit === "1") $("#song-title").text(`${songName} [E]`);
		else $("#song-title").text(songName);
		$("#song-album").text(albumName);
		$("#song-artist").text(artistName);
		$("#song-duration").text(duration);
		$("#song-art").attr("src", albumArt);
		$("#song-last-update").text(updated);

		if (button.getAttribute("data-bs-instrumental") === "1") {
			$("#instrumental-message").removeClass("d-none");
			$("#save-button").prop("disabled", true);
		} else if ((plain === synced) === '0') {
			$("#no-lyrics-message").removeClass("d-none");
			$("#save-button").prop("disabled", true);
		} else {
			if (synced === '0') $("#download-link-synced").addClass("disabled");
			else {
				$("#download-link-synced").removeClass("disabled");
				syncedContents =
					`[ar: ${artistName}]\n` +
					`[ti: ${songName}]\n` +
					`[al: ${albumName}]\n` +
					`[by: Musixmatch]\n`;
			}
			if(richsync==='0') $("#download-link-richsync").addClass('disabled');
			else{
				$("#download-link-richsync").removeClass("disabled");
				richsyncContents =
					`[ar: ${artistName}]\n` +
					`[ti: ${songName}]\n` +
					`[al: ${albumName}]\n` +
					`[by: Musixmatch (Word-by-Word)]\n`;
			}

			// Set file name and contents on save
			fileName = `${artistName} - ${songName}`;
			$.ajax({
				method: "GET",
				url: `/musixmatch/${songID}/lyrics`,
				beforeSend: function () {
					$("#save-button").prop("disabled", true);
					$(".placeholder-glow").removeClass("d-none");
					$.LoadingOverlay("show");
				},
				complete: function () {
					$("#save-button").prop("disabled", false);
					$(".placeholder-glow").addClass("d-none");
					$.LoadingOverlay("hide");
				},
				success: function (data) {
					plainContents = `${fileName}\n\n${data.content}`;
					$("#lyrics-content").html(data.content.replace(/\n/g, "<br/>"));
					$("#song-copyright").text(data.copyright);
				},
				error: function (xhr, st) {
					$("#error-message").text(xhr.responseJSON.message ?? st);
					$("#error-alert").removeClass("d-none");
				}
			});
		}
	});
}
plainDL.onclick = function () {
	plainDL.href =
		`data:text/plain;charset=utf-8,${encodeURIComponent(plainContents)}`;
	plainDL.download = `${fileName}.txt`;
};
syncedDL.onclick = function (e) {
	e.preventDefault();
	$.ajax({
		method: "GET",
		url: `/musixmatch/${songID}/subtitle`,
		beforeSend: function () {
			$("#save-button").prop("disabled", true);
			$("#error-alert").addClass("d-none");
			$.LoadingOverlay("show");
		},
		complete: function () {
			$("#save-button").prop("disabled", false);
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			blobDL(`${syncedContents}[length: ${data.duration}]\n${data.content}`,`${fileName}.lrc`);
		},
		error: function (xhr, st) {
			$("#error-message").text(xhr.responseJSON.message ?? st);
			$("#error-alert").removeClass("d-none");
		}
	});
};
richsyncDL.onclick = function (e) {
	e.preventDefault();
	const confirmDL=confirm('Only a few players supports Word-by-Word lyrics. Continue download?');
	if(confirmDL){
		$.ajax({
			method: "GET",
			url: `/musixmatch/${songID}/richsync`,
			beforeSend: function () {
				$("#save-button").prop("disabled", true);
				$("#error-alert").addClass("d-none");
				$.LoadingOverlay("show");
			},
			complete: function () {
				$("#save-button").prop("disabled", false);
				$.LoadingOverlay("hide");
			},
			success: function (data) {
				blobDL(`${richsyncContents}[length: ${data.duration}]\n${data.content}`,`${fileName}.lrc`);
			},
			error: function (xhr, st) {
				$("#error-message").text(xhr.responseJSON.message ?? st);
				$("#error-alert").removeClass("d-none");
			}
		});
	}
};
function blobDL(data, filename){
	const blob = new Blob([data],{type: 'text/plain', charset: 'utf-8'});
	let url = window.URL.createObjectURL(blob);
	let a = document.createElement("a");
	a.href = url;
	a.download = filename;
	document.body.appendChild(a);
	a.click();
	a.remove();
	window.URL.revokeObjectURL(url);
}