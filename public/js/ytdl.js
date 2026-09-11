/* global toast */
$("#yt-video-form").on("submit", function (event) {
	event.preventDefault();
	sendAjax($(this).serialize(), "/dl/youtube/video");
});
$("#yt-audio-form").on("submit", function (event) {
	event.preventDefault();
	sendAjax($(this).serialize(), "/dl/youtube/audio");
});
$("#yt-alt-form").on("submit", function (event) {
	event.preventDefault();
	sendAjax($(this).serialize(), "/dl/youtube/alt");
});
function sendAjax(data, url) {
	$("#ytdl-loader").removeClass("d-none");
	$("form :input").prop("disabled", true);
	$.ajax({
		url: url,
		data: data
	})
		.done(function (r) {
			$("#ytdl-container").html(r.html);
		})
		.fail(function (xhr, st, err) {
			console.warn(err);
			toast.fire({
				icon: "error",
				text:
					st === "timeout"
						? "Connection timed out"
						: (xhr.responseJSON?.message ?? "Server connection was lost")
			});
		})
		.always(function () {
			$("#ytdl-loader").addClass("d-none");
			$("form :input").prop("disabled", false);
		});
}
