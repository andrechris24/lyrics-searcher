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
	const fileName = `${artistName} - ${songName}`,
		meta = `[id: ${songID}]\n[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n[by: ${user}]\n[length: ${duration}]\n[offset: ${offset}]\n`;
	if (!content.match(/\[(\d+):(\d+).(\d+)\]/)) {
		ext = ".txt";
		blobDL(`${fileName}\n\n${content}`, fileName + ext);
	} else {
		ext = ".lrc";
		if (content.match(/<(\d+):(\d+).(\d+)>/g)) {
			Swal.fire({
				icon: "question",
				title:
					"This lyric contains word-by-word or syllable timestamps. Do you want to keep them?",
				theme: "bootstrap-5",
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: "Yes",
				denyButtonText: "No",
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
