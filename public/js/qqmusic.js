/* global blobDL, toast, Swal */
let fileName, message, ext, lyricContent;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}`;
	$.ajax({
		url: `/qqmusic/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if (data.encoded === true) {
				Swal.fire({
					title: "Word-by-word lyric detected",
					text: "Select lyric type to download, then click OK. Please note that only a few players support word-by-word lyrics.",
					theme: "bootstrap-5",
					buttonsStyling: false,
					customClass: {
						confirmButton: "btn btn-primary btn-lg me-2",
						cancelButton: "btn btn-danger btn-lg",
					},
					topLayer: true,
					inputOptions: {
						synced: "Synced",
						wordbyword: "Word-by-Word",
						plain: "Plain",
					},
					input: "select",
					inputPlaceholder: "Select lyric type",
					showCancelButton: true,
					inputValidator: (value) => {
						return new Promise((resolve) => {
							if (!value) resolve("Please select lyric type to continue");
							else resolve();
						});
					},
				}).then((result) => {
					if (result.isConfirmed && result.value) {
						switch (result.value) {
							case "synced":
								lyricContent = data.lyric.replace(/<(\d+):(\d+).(\d+)>/g, "");
								ext = ".lrc";
								break;
							case "wordbyword":
								lyricContent = data.lyric;
								ext = ".lrc";
								break;
							default: //plain or unknown
								lyricContent = data.lyric
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
			} else if (data.lyric.match(/\[(\d+):(\d+).(\d+)\]/))
				blobDL(`[id: ${data.id}]\n${data.lyric}`, `${fileName}.lrc`);
			else {
				toast.fire({ icon: "warning", text: "Plain lyric detected" });
				blobDL(`${fileName}\n\n${data.lyric}`, `${fileName}.txt`);
			}
		},
		error: function (xhr, st, err) {
			console.warn(err);
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON?.message ?? err ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
});
