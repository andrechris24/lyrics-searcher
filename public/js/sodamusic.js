let fileName, message, contents, ext, lyricContent;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		albumName = $(this).data("album"),
		duration = $(this).data("duration"),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}`;
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
		success: function (data) {
			if (data.type === "krc") {
				Swal.fire({
					title: "Choose lyric type to download",
					text: "Note: only a few players support word-by-word lyrics.",
					theme: "bootstrap-5",
					buttonsStyling: false,
					customClass: {
						confirmButton: "btn btn-primary btn-lg me-2",
						cancelButton: "btn btn-danger btn-lg"
					},
					topLayer: true,
					inputOptions: {
						synced: "Synced",
						wordbyword: "Word-by-Word",
						plain: "Plain"
					},
					input: "select",
					inputPlaceholder: "Select lyric type",
					showCancelButton: true,
					inputValidator: (value) => {
						return new Promise((resolve) => {
							if (!value) resolve("Please select lyric type to continue");
							else resolve();
						});
					}
				}).then((result) => {
					if (result.isConfirmed && result.value) {
						switch (result.value) {
							case "synced":
								lyricContent =
									contents + data.content.replace(/<(\d+):(\d+).(\d+)>/g, "");
								ext = ".lrc";
								break;
							case "wordbyword":
								lyricContent = contents + data.content;
								ext = ".lrc";
								break;
							default: //plain or unknown
								lyricContent = data.content
									.replace(/<(\d+):(\d+).(\d+)>/g, "")
									.replace(/\[(\d+):(\d+).(\d+)\]/g, "");
								ext = ".txt";
								break;
						}
						if (ext === ".txt")
							blobDL(`${fileName}\n\n${lyricContent}`, fileName + ext);
						else if (typeof data.id !== "undefined")
							blobDL(`[id: ${data.id}]\n${lyricContent}`, fileName + ext);
						else blobDL(lyricContent, fileName + ext);
					}
				});
			} else {
				contents += data.content;
				blobDL(contents, `${fileName}.lrc`);
			}
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
});
