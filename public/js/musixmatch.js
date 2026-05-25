/* global blobDL, toast, bootstrap */
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

$(".download-btn").on("click", function (e) {
	e.preventDefault();
	const id = $(this).data("id"),
		type = $(this).data("type"),
		artist = $(this).data("artist"),
		title = $(this).data("title"),
		album = $(this).data("album");
	const fileName = `${artist} - ${title}`;
	let contents, ext, message;
	$.ajax({
		url: `/musixmatch/${id}/${type}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if (type === "lyrics") {
				contents = `${fileName}\n\n`;
				ext = "txt";
			} else {
				contents =
					`[id: ${data.id}]\n[ar: ${artist}]\n[ti: ${title}]\n` +
					`[al: ${album}]\n[by: Musixmatch]\n[length: ${data.duration}]\n`;
				ext = "lrc";
			}
			blobDL(contents + data.content, `${fileName}.${ext}`);
		},
		error: function (xhr, st, err) {
			console.warn(err);
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON?.message ?? err ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
});
