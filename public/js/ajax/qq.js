/* global blobDL, toast, Swal */
let fileName, ext, lyricContent;
$("#two-form-search").on("submit", function (e) {
	e.preventDefault();
	const query = $(this).serialize();
	$("#qq-container").html("");
	$("#qq-loader").removeClass("d-none");
	sendAjax(query);
});
function sendAjax(data) {
	$.ajax({
		url: "/qqmusic/results",
		method: "GET",
		data: data,
		beforeSend: function () {
			$("form :input").prop("disabled", true);
		},
		complete: function () {
			$("#qq-loader").addClass("d-none");
			$("#qq-container").LoadingOverlay("hide");
		},
		success: function (r) {
			$("#qq-container").html(r.html);
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
						try {
							let fileMime = 'text/plain';
							if (data.encoded === true) {
								Swal.fire({
									title: "Word-by-word lyric detected",
									text: "Select lyric type to download, then click OK. To import lyric into Aegisub, choose QRC Raw or Synced. Please note that only a few players support word-by-word lyrics.",
									footer:
										'<a href="https://github.com/qwe7989199/Lyric-Importer-for-Aegisub">Additional script for Aegisub (Buggy for KRC)</a>',
									theme: "bootstrap-5",
									buttonsStyling: false,
									customClass: {
										confirmButton: "btn btn-primary btn-lg me-2",
										cancelButton: "btn btn-danger btn-lg"
									},
									topLayer: true,
									inputOptions: {
										wordbyword: "Word-by-Word",
										synced: "Synced",
										plain: "Plain",
										raw: "QRC Raw"
									},
									input: "select",
									inputPlaceholder: "Select lyric type",
									showCancelButton: true,
									inputValidator: (value) => {
										return new Promise((resolve) => {
											if (!value)
												resolve("Please select lyric type to continue");
											else resolve();
										});
									}
								}).then((result) => {
									if (result.isConfirmed && result.value) {
										switch (result.value) {
											case "synced":
												lyricContent = data.lyric.replace(
													/<(\d+):(\d+).(\d+)>/g,
													""
												);
												ext = ".lrc";
												break;
											case "wordbyword":
												lyricContent = data.lyric;
												ext = ".lrc";
												break;
											case "raw":
												lyricContent = data.raw;
												ext = ".qrc";
												break;
											default: //plain or unknown
												lyricContent = data.lyric
													.replace(/<(\d+):(\d+).(\d+)>/g, "")
													.replace(/\[(\d+):(\d+).(\d+)\]/g, "");
												ext = ".txt";
												break;
										}
										if (ext === ".txt")
											lyricContent = `${fileName}\n\n${lyricContent}`;
										else if (ext === '.qrc') {
											const sig = "[offset:0]\n";
											let arrBuf = new Uint8Array(lyricContent.length / 2 + sig.length);
											for (let i = 0; i < sig.length; ++i)
												arrBuf[i] = sig.charCodeAt(i);
											const offset = sig.length;
											for (let i = 0; i < lyricContent.length; i += 2) {
												arrBuf[offset + i / 2] = parseInt(lyricContent.slice(i, i + 2), 16);
											}
											lyricContent = arrBuf.buffer;
											fileMime = "application/octet-stream";
										} else
											lyricContent = `[id:${data.id ?? songID}]\n${lyricContent}`;
										blobDL(lyricContent, fileName + ext, fileMime);
									}
								});
							} else if (data.lyric.match(/\[(\d+):(\d+).(\d+)\]/))
								blobDL(
									`[id:${data.id ?? songID}]\n${data.lyric}`,
									`${fileName}.lrc`,
									fileMime
								);
							else {
								toast.fire({ icon: "warning", text: "Plain lyric detected" });
								blobDL(`${fileName}\n\n${data.lyric}`, `${fileName}.txt`);
							}
						} catch (e) {
							console.error(e);
							toast.fire({
								icon: "error",
								text: "Script error detected while downloading lyric. Please contact site owner."
							});
						}
					},
					error: function (xhr, st, err) {
						console.warn(`${st}: ${err}`);
						if (xhr.status === 422) {
							if (typeof xhr.responseJSON.errors.title !== "undefined")
								$("#track-name").addClass("is-invalid");
							if (typeof xhr.responseJSON.errors.artist !== "undefined")
								$("#artist-name").addClass("is-invalid");
						}
						toast.fire({
							icon: "error",
							text:
								st === "timeout"
									? "Connection timed out"
									: (xhr.responseJSON?.message ?? "Server connection was lost")
						});
					}
				});
			});
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
		}
	});
}
