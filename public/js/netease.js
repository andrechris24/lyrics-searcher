/* global toast, zpad */
let lyricContents, klyricContent, fileName, message, ext;
const lyricsModal = document.getElementById("modalLyrics"),
	lyricDL = document.getElementById("dl-synced"),
	klyricDL = document.getElementById("dl-klyric");
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		const songName = button.getAttribute("data-bs-title"),
			artistName = button.getAttribute("data-bs-artist"),
			albumName = button.getAttribute("data-bs-album"),
			songID = button.getAttribute("data-bs-id"),
			duration = button.getAttribute("data-bs-duration");
		const metaLyric =
			`[ar: ${artistName}]\n[ti: ${songName}]\n[al: ${albumName}]\n` +
			`[by: NetEase]\n[length: ${duration}]\n`;
		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback

		// Update the modal's content
		$("#song-album").text(albumName);
		$("#song-duration").text(duration);
		$("#song-title").text(songName);
		$("#song-artist").text(artistName);

		// Set file name and contents on save
		fileName = `${artistName} - ${songName}`;
		$.ajax({
			url: `/netease/${songID}`,
			beforeSend: function () {
				$(".placeholder-glow").removeClass("d-none");
				$("#lyrics-content").text("");
			},
			complete: function () {
				$(".placeholder-glow").addClass("d-none");
			},
			success: function (data) {
				if (typeof data.klyric !== "undefined") {
					if (data.klyric.lyric !== "") {
						$("#dl-klyric").removeClass("disabled");
						klyricContent = `${metaLyric}[ve: ${data.klyric.version}]\n${parseKLyric(data.klyric.lyric)}`;
					} else {
						$("#dl-klyric").addClass("disabled");
						klyricContent = "";
					}
				} else {
					$("#dl-klyric").addClass("disabled");
					klyricContent = "";
				}
				if (!data.lrc.lyric.match(/\[(\d+):(\d+).(\d+)\]/)) {
					lyricContents = `${fileName}\n\n${data.lrc.lyric}`;
					ext = "txt";
				} else {
					lyricContents = `${metaLyric}[ve: ${data.lrc.version??1.0}]\n${data.lrc.lyric}`;
					ext = "lrc";
				}
				$("#lyrics-content").text(data.lrc.lyric);
			},
			error: function (xhr, st, err) {
				console.warn(err);
				if (st === "timeout") message = "Connection timed out";
				else message = xhr.responseJSON?.message ?? err ?? st;
				toast.fire({ icon: "error", text: message });
				$("#modalLyrics").modal("hide");
			}
		});
	});
}
lyricDL.onclick = function () {
	lyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(lyricContents)}`;
	lyricDL.download = `${fileName}.${ext}`;
};
klyricDL.onclick = function () {
	klyricDL.href = `data:text/plain;charset=utf-8,${encodeURIComponent(klyricContent)}`;
	klyricDL.download = `${fileName}.lrc`;
};
function parseKLyric(lyricText) {
	let enhancedlyricText = "";
	let matches;
	let metaRegex = /^\[(\S+):(\S+)\]$/,
		timestampsRegex = /^\[(\d+),(\d+)\]/,
		timestamps2Regex = /\((\d+),(\d+)\)([^(]*)/g,
		lines = lyricText.split(/[\n]/);
	for (const line of lines) {
		if ((matches = metaRegex.exec(line))) {
			// meta info
			enhancedlyricText += `${matches[0]}\n`;
		} else if ((matches = timestampsRegex.exec(line))) {
			let startTime = parseInt(matches[1]);
			let duration = parseInt(matches[2]);
			let lyricLine = `[${formatTime(startTime)}]`;
			// parse sub-timestamps
			let subMatches;
			let subStartTime = startTime;
			while ((subMatches = timestamps2Regex.exec(line))) {
				let subDuration = parseInt(subMatches[2]);
				let subWord = subMatches[3];
				lyricLine += `<${formatTime(subStartTime)}>${subWord}`;
				subStartTime += subDuration;
			}
			enhancedlyricText += `${lyricLine}<${formatTime(startTime + duration)}> \n`;
		}
	}
	return enhancedlyricText;
}
function formatTime(time) {
	// const zpad = (n) => {
	// 	const s = n.toString();
	// 	return s.length < 2 ? `0${s}` : s;
	// };
	let t = Math.abs(time / 1000);
	const h = Math.floor(t / 3600);
	t -= h * 3600;
	const m = Math.floor(t / 60);
	t -= m * 60;
	const s = Math.floor(t);
	const ms = t - s;
	return `${(h ? `${zpad(h)}:` : "") + zpad(m)}:${zpad(s)}.${zpad(Math.floor(ms * 100))}`;
}
