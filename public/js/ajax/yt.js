/* global blobDL, toast, basicForm, coreui */
$(basicForm).submit(function (event) {
	event.preventDefault();
	const formData = $(this).serialize();
	$("#yt-container").html("");
	$("form :input").prop("disabled", true);
	$("#yt-loader").removeClass("d-none");
	$.ajax({ url: "/youtube/results", data: formData })
		.done(function (r) {
			$("#yt-container").html(r.html);
			const openDLTooltip = document.querySelectorAll(
				'[data-coreui-toggle="tooltip"]'
			);
			// eslint-disable-next-line no-unused-vars
			const openDLList = [...openDLTooltip].map(
				(tooltipTriggerEl) => new coreui.Tooltip(tooltipTriggerEl)
			);
			$(".download-btn").on("click", function (e) {
				e.preventDefault();
				const songName = $(this).data("title"),
					artistName = $(this).data("artist"),
					duration = $(this).data("duration"),
					songID = $(this).data("id");
				const fileName = `${artistName} - ${songName}`,
					meta = `\n[ar: ${artistName}]\n[ti: ${songName}]\n[length: ${duration}]\n[by: YouTube]\n`;
				$.ajax({
					url: `/youtube/${songID}`,
					beforeSend: function () {
						$.LoadingOverlay("show");
					},
					complete: function () {
						$.LoadingOverlay("hide");
					},
					success: function (data) {
						if (data.lyric.match(/\[(\d+):(\d+).(\d+)\]/))
							blobDL(`[id: ${data.id}]${meta}${data.lyric}`, `${fileName}.lrc`);
						else {
							toast.fire({ icon: "warning", text: "Plain lyric detected" });
							blobDL(`${fileName}\n\n${data.lyric}`, `${fileName}.txt`);
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
			$("#yt-loader").addClass("d-none");
		});
});
