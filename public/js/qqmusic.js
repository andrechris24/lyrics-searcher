let fileName, message, ext;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}.`;
	$.ajax({
		url: `/qqmusic/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if(!data.lyric.match(/\[(\d+):(\d+).(\d+)\]/)){
				toast.fire({icon: "warning", text: "Downloading lyric in plain format"});
				ext = "txt";
			}else ext="lrc";
			blobDL(data.lyric, fileName+ext);
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
});
