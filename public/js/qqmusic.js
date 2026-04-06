let fileName, message;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}.lrc`;
	$.ajax({
		url: `/qqmusic/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			blobDL(data.lyric, fileName);
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
});
