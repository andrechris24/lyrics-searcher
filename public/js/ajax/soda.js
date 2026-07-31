/* global blobDL, toast, Swal, basicForm */
let fileName, contents, ext, lyricContent;
$(basicForm).on("submit", function (e) {
	e.preventDefault();
	const query = $(this).serialize();
	sendAjax(query);
	$("#sodamusic-container").html("");
	$("#sodamusic-loader").removeClass("d-none");
});
function sendAjax(data) {
	$.ajax({
		url: "/sodamusic/results",
		method: "GET",
		data: data,
		beforeSend: function () {
			$("form :input").prop("disabled", true);
		},
		complete: function () {
			$("#sodamusic-loader").addClass("d-none");
			$("#sodamusic-container").LoadingOverlay("hide");
		},
		success: function (data) {
			$("#sodamusic-container").html(data.html);
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
									wordbyword: "Word-by-Word",
									synced: "Synced",
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
												contents +
												data.content.replace(/<(\d+):(\d+).(\d+)>/g, "");
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
					error: function (xhr, st, err) {
						console.warn(err);
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
		}
	});
}
// eslint-disable-next-line no-unused-vars
function navigate(query, offset) {
	sendAjax({ query: query, offset: offset });
	$("#sodamusic-container").LoadingOverlay("show");
	$("html, body").animate({scrollTop: $("#sodamusic-container").offset().top},200);
}
