let fileName, message, contents;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		albumName = $(this).data('album'),
		duration=$(this).data('duration'),
		songID = $(this).data("id");
	fileName = `${artistName} - ${songName}.lrc`;
	contents=`[ti: ${songName}]\n[ar: ${artistName}]\n[al: ${albumName}]\n[length: ${duration}]\n[by: Soda Music]`;
	$.ajax({
		url: `/sodamusic/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			console.info('Downloading in Enhanced LRC format');
			blobDL(`${contents}\n${data.lyric.content}`, fileName);
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
});
