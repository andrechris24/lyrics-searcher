/* global blobDL, toast */
$("#mx-basic-form").submit(function (event) {
	event.preventDefault();
	$("#mx-loader").removeClass("d-none");
	$("#mx-container").html("");
	sendAjax("/musixmatch/results", $(this).serialize());
});
$("#mx-advanced-form").submit(function (event) {
	event.preventDefault();
	$("#mx-loader").removeClass("d-none");
	$("#mx-container").html("");
	sendAjax("/musixmatch/advanced/results", $(this).serialize());
});
$("#chart-form").submit(function (event) {
	event.preventDefault();
	$("#mx-loader").removeClass("d-none");
	sendAjax("/musixmatch/charts/list", $(this).serialize());
});
function sendAjax(url, data) {
	$("form :input").prop("disabled", true);
	$.ajax({ url: url, data: data })
		.done(function (r) {
			$("#mx-container").html(r.html);
			$(".download-btn").on("click", function (e) {
				e.preventDefault();
				const id = $(this).data("id"),
					type = $(this).data("type"),
					artist = $(this).data("artist"),
					title = $(this).data("title"),
					album = $(this).data("album");
				const fileName = `${artist} - ${title}`;
				let contents, ext;
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
			try {
				if (xhr.status === 422) {
					if (typeof xhr.responseJSON.errors.title !== "undefined")
						$("#track-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.artist !== "undefined")
						$("#artist-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.lyrics !== "undefined")
						$("#lyric-keyword").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.chart !== "undefined")
						$("#chart-type").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.type !== "undefined")
						$("#search-type").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.query !== "undefined")
						$("#basic-search-query").addClass("is-invalid");
				}
				toast.fire({
					icon: "error",
					text:
						st === "timeout"
							? "Connection timed out"
							: (xhr.responseJSON?.message ?? "Server connection was lost")
				});
			} catch (e) {
				console.error(e);
				toast.fire({
					icon: "error",
					text: "An error occurred while marking input errors. Please contact site owner."
				});
			}
		})
		.always(function () {
			$("#mx-container").LoadingOverlay("hide");
			$("#mx-loader").addClass("d-none");
		});
}
// eslint-disable-next-line no-unused-vars
function navigate(url, data) {
	$("#mx-container").LoadingOverlay("show");
	sendAjax(url, data);
}
