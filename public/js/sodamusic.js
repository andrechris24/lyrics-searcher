let fileName, message, contents;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		albumName = $(this).data("album"),
		duration = $(this).data("duration"),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}.lrc`;
	contents =
		`[ti: ${songName}]\n[ar: ${artistName}]\n` +
		`[al: ${albumName}]\n[length: ${duration}]\n[by: Soda Music]\n`;
	$.ajax({
		url: `/sodamusic/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: async function (data) {
			try {
				if (data.lyric.type === "krc") {
					const choice = await Swal.fire({
						title: "Choose lyric type to download",
						text: "Note: only a few players support word-by-word lyrics.",
						theme: "bootstrap-5",
						showDenyButton: true,
						showCancelButton: true,
						confirmButtonText: "Synced",
						denyButtonText: "Word-by-Word",
					});
					if (choice.isConfirmed)
						contents += data.lyric.content.replace(/<\d{2}:\d{2}\.\d{2}>/g, "");
					else if (choice.isDenied) contents += data.lyric.content;
					else if (choice.isDismissed) return false;
					else throw "Unknown choice";
				} else contents += data.lyric.content;
				blobDL(contents, `${fileName}`);
			} catch (e) {
				console.warn(e);
			}
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		},
	});
});
