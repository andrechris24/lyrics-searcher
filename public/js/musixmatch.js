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
				contents = fileName;
				ext = "txt";
			} else {
				contents =
					`[id: ${data.id}]\n[ar: ${artist}]\n[ti: ${title}]\n` +
					`[al: ${album}]\n[by: Musixmatch]\n[length: ${data.duration}]\n`;
				ext = "lrc";
			}
			blobDL(contents + data.content, `${fileName}.${ext}`);
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		},
	});
});
