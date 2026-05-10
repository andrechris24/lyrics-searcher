function krc2lrc(krcText) {
	let lyricText = "";
	let matches;
	const metaRegex = /^\[(\S+):(\S+)\]$/,
		timestampsRegex = /^\[(\d+),(\d+)\]/,
		timestamps2Regex = /<(\d+),(\d+),(\d+)>([^<]*)/g,
		lines = krcText.split(/[\r\n]/);
	for (const line of lines) {
		if ((matches = metaRegex.exec(line))) {
			// meta info
			if (matches[1] == "language") {
				const langObj = JSON.parse(atob(matches[2]));
				const contentArrayObj = langObj["content"] || [];
				if (contentArrayObj.length == 0 || contentArrayObj[0].type != 1)
					continue;
				continue;
			}
			lyricText += `${matches[0]}\r\n`;
		} else if ((matches = timestampsRegex.exec(line))) {
			const startTime = parseInt(matches[1]),
				duration = parseInt(matches[2]);
			let lyricLine = `[${formatTime(startTime)}]`;
			// parse sub-timestamps
			let subMatches;
			while ((subMatches = timestamps2Regex.exec(line))) {
				const offset = parseInt(subMatches[1]);
				const subWord = subMatches[4];
				lyricLine += `<${formatTime(startTime + offset)}>${subWord}`;
			}
			lyricLine += `<${formatTime(startTime + duration)}> `;
			lyricText += `${lyricLine}\r\n`;
		}
	}
	return lyricText;
}

function qrcToLrc(qrcText) {
	if (qrcText == null) return null;

	return qrcText
		.replace(
			/^\[(\d+),(\d+)\]/gm,
			(_, base) => `[${formatTime(+base)}]<${formatTime(+base)}>`
		)
		.replace(
			/\((\d+),(\d+)\)/g,
			(_, start, offset) => `<${formatTime(+start + +offset)}>`
		);
}

/**
 * Converts SubRip subtitles into array of objects
 * [{
 *     id:        `Number of subtitle`
 *     startTime: `Start time of subtitle`
 *     endTime:   `End time of subtitle
 *     text: `Text of subtitle`
 * }]
 *
 * @param  {String}  data SubRip suntitles string
 * @param  {Boolean} ms   Optional: use milliseconds for startTime and endTime
 * @return {Array}
 */
function fromSrt(data, ms) {
	const useMs = ms ? true : false;
	data = data.replace(/\r/g, "");
	const regex =
		/(\d+)\n(\d{2}:\d{2}:\d{2},\d{3}) --> (\d{2}:\d{2}:\d{2},\d{3})/g;
	data = data.split(regex);
	data.shift();
	let items = [];
	for (let i = 0; i < data.length; i += 4) {
		items.push({
			id: data[i].trim(),
			startTime: useMs
				? timeMilliseconds(data[i + 1].trim())
				: data[i + 1].trim(),
			endTime: useMs
				? timeMilliseconds(data[i + 2].trim())
				: data[i + 2].trim(),
			text: data[i + 3].trim()
		});
	}
	return items;
}

function zpad(n) {
	const s = n.toString();
	return s.length < 2 ? `0${s}` : s;
}

function formatTime(time) {
	let str;
	if (isNaN(time)) {
		const srtTime = time.match(/^(\d{2}):(\d{2}):(\d{2}),(\d{3})$/);
		let hours = parseInt(srtTime[1], 10);
		let minutes = parseInt(srtTime[2], 10) + hours * 60; // LRC doesn't use hours
		let seconds = parseInt(srtTime[3], 10);
		let centiseconds = Math.floor(parseInt(srtTime[4], 10) / 10); // mmm → xx
		str = `${(hours ? `${zpad(hours)}:` : "") + zpad(minutes)}:${zpad(seconds)}.${zpad(centiseconds)}`;
	} else {
		let t = Math.abs(time / 1000);
		let h = Math.floor(t / 3600);
		t -= h * 3600;
		let m = Math.floor(t / 60);
		t -= m * 60;
		let s = Math.floor(t);
		let ms = t - s;
		str = `${(h ? `${zpad(h)}:` : "") + zpad(m)}:${zpad(s)}.${zpad(Math.floor(ms * 100))}`;
	}
	return str;
}
function timeMilliseconds(val) {
	const measures = [3600000, 60000, 1000];
	let time = [];
	for (let i in measures) {
		let res = ((val / measures[i]) >> 0).toString();
		if (res.length < 2) res = `0${res}`;
		val %= measures[i];
		time.push(res);
	}
	let ms = val.toString();
	if (ms.length < 3) {
		for (let i = 0; i <= 3 - ms.length; i++) ms = `0${ms}`;
	}
	return `${time.join(":")},${ms}`;
}
$("#lyric-converter-form").on("submit", function (e) {
	e.preventDefault();
	const lyricContent = $("#lyrics-content-form").val();
	let lrcText = "";
	switch ($("#convert-type").val()) {
		case "fromSrt": {
			let srtBlocks = fromSrt(lyricContent, false);
			for (const block of srtBlocks) {
				lrcText +=
					`[${formatTime(block.startTime)}]` + block.text.replace(/\n/g, " ");
				lrcText += `\r\n[${formatTime(block.endTime)}]\r\n`;
			}
			break;
		}
		case "fromKrc":
			lrcText = krc2lrc(lyricContent);
			break;
		case "fromQrc":
			lrcText = qrcToLrc(lyricContent);
			break;
		default:
			toast.fire({ icon: "error", text: "Unknown parameter" });
			break;
	}
	$("#converted-lyric").text(lrcText);
	if (lrcText !== "") $("#save-converted").prop("disabled", false);
	else $("#save-converted").prop("disabled", true);
});
$("#save-converted").on("click", function () {
	let ext;
	const lrcContent = $("#converted-lyric").text();
	if (
		lrcContent.match(
			/(\d+)\n(\d{2}:\d{2}:\d{2},\d{3}) --> (\d{2}:\d{2}:\d{2},\d{3})/,
		)
	)
		ext = "srt";
	else if (lrcContent.match(/\[(\d+):(\d+).(\d+)\]/)) ext = "lrc";
	else ext = "txt";
	blobDL(lrcContent, `converted.${ext}`);
});
