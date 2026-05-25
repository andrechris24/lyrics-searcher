/* global blobDL, Swal, toast */
let ext;
$(".list-group-item-action").on("click", function (e) {
	e.preventDefault();
	const songName = $(this).data("title"),
		artistName = $(this).data("artist"),
		albumName = $(this).data("album"),
		duration = $(this).data("duration"),
		content = $(this).data("content"),
		user = $(this).data("user"),
		offset = $(this).data("offset"),
		songID = $(this).data("id");
	const fileName = `${artistName} - ${songName}`;
	if (!content.match(/\[(\d+):(\d+).(\d+)\]/)) {
		ext = ".txt";
		blobDL(`${fileName}\n\n${content}`, fileName + ext);
	} else {
		const meta = `[id: ${songID}]\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n[by: ${user}]\n[length: ${duration}]\n[offset: ${offset}]\n`;
		ext = ".lrc";
		if (content.match(/<(\d+):(\d+).(\d+)>/g)) {
			Swal.fire({
				icon: "question",
				title:
					"This lyric contains syllable timestamps and only a few players supports this type. Do you want to keep them?",
				theme: "bootstrap-5",
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: "Yes",
				denyButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					blobDL(meta + content, fileName + ext);
				} else if (result.isDenied) {
					const plainContent = content.replace(/<(\d+):(\d+).(\d+)>/g, "");
					blobDL(meta + plainContent, fileName + ext);
				} else console.warn("Download cancelled");
			});
		} else blobDL(meta + content, fileName + ext);
	}
});
$("#uploadLyricForm").on("submit", function (e) {
	e.preventDefault();
	$.ajax({
		headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
		method: "POST",
		data: new FormData(this),
		url: `/local/upload`,
		processData: false,
		contentType: false,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			toast.fire({ icon: data.status, text: data.message });
			$("#uploadLyricForm")[0].reset();
		},
		error: function (xhr, st, err) {
			toast.fire({
				icon: "error",
				text: xhr.responseJSON?.message ?? err ?? st
			});
		}
	});
});
