/* global blobDL, toast */
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
				text: st==='timeout'?'Connection timed out': xhr.responseJSON?.message??err??st
			});
		}
	});
});
