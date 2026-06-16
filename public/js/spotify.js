/* global blobDL, toast */
$(".download-btn").on("click", function (e) {
	e.preventDefault();
	let message;
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		albumName = $(this).data("album"),
		duration = $(this).data("duration"),
		songID = $(this).data("id");
	const fileName = `${artistName} - ${songName}`,
		meta=`\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n[length: ${duration}]\n[by: Spotify]\n`;
	$.ajax({
		url: `/spotify/${songID}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if(data.lyric.match(/\[(\d+):(\d+).(\d+)\]/))
				blobDL(`[id: ${data.id}]${meta}${data.lyric}`, `${fileName}.lrc`);
			else{
				toast.fire({icon: 'warning',text: 'Plain lyric detected'});
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
