/* global blobDL, toast, pako, zpad */
function xorKRC(rawData) {
	if (null == rawData) return;

	let dataView = new Uint8Array(rawData);
	let magicBytes = [0x6b, 0x72, 0x63, 0x31]; // 'k' , 'r' , 'c' ,'1'
	if (dataView.length < magicBytes.length) return;

	for (let i = 0; i < magicBytes.length; ++i) {
		if (dataView[i] != magicBytes[i]) return;
	}
	let decryptedData = new Uint8Array(dataView.length - magicBytes.length);
	let encKey = [
		0x40, 0x47, 0x61, 0x77, 0x5e, 0x32, 0x74, 0x47, 0x51, 0x36, 0x31, 0x2d,
		0xce, 0xd2, 0x6e, 0x69
	];
	let hdrOffset = magicBytes.length;
	for (let i = hdrOffset; i < dataView.length; ++i) {
		let x = dataView[i];
		let y = encKey[(i - hdrOffset) % encKey.length];
		decryptedData[i - hdrOffset] = x ^ y;
	}
	return decryptedData;
}

function krc2lrc(krcText) {
	let lyricText = "",
		matches;
	const metaRegex = /^\[(\S+):(\S+)\]$/,
		timestampsRegex = /^\[(\d+),(\d+)\]/,
		timestamps2Regex = /<(\d+),(\d+),(\d+)>([^<]*)/g,
		lines = krcText.split(/[\n]/);
	for (const line of lines) {
		if ((matches = metaRegex.exec(line))) {
			// meta info
			if (matches[1] == "language") continue;
			lyricText += `${matches[0]}\n`;
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
			lyricText += `${lyricLine}<${formatTime(startTime + duration)}> \n`;
		}
	}
	return lyricText;
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

function formatTime(time) {
	let str;
	if (isNaN(time)) {
		const srtTime = time.match(/^(\d{2}):(\d{2}):(\d{2}),(\d{3})$/);
		let hours = parseInt(srtTime[1], 10),
			minutes = parseInt(srtTime[2], 10) + hours * 60,
			seconds = parseInt(srtTime[3], 10),
			centiseconds = Math.floor(parseInt(srtTime[4], 10) / 10); // mmm → xx
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
	const fileContent = $("#source-file-to-convert")[0].files[0];
	const fileReader = new FileReader();
	fileReader.onerror = function (e) {
		console.warn(e);
		toast.fire({ icon: "error", text: "Failed to read file" });
	};
	fileReader.onload = function (e) {
		let lrcText = "";
		if (e.target.result instanceof ArrayBuffer) {
			let krcContents = xorKRC(e.target.result);
			if (!krcContents) {
				toast.fire({ icon: "error", text: "Failed to decode KRC file" });
				return;
			}
			krcContents = pako.inflate(krcContents.buffer, { to: "string" });
			if (!krcContents) {
				toast.fire({ icon: "error", text: "Failed to unpack KRC file" });
				return;
			}
			lrcText = krc2lrc(krcContents);
		} else {
			let srtBlocks = fromSrt(e.target.result, false);
			for (const block of srtBlocks) {
				lrcText += `[${formatTime(block.startTime)}]${block.text.replace(/\n/g, " ")}`;
				lrcText += `\n[${formatTime(block.endTime)}]\n`;
			}
		}
		$("#converted-lyric").text(lrcText);
		$("#converted-lyric").trigger('focus');
		if (lrcText !== "") $("#save-converted").prop("disabled", false);
		else $("#save-converted").prop("disabled", true);
	};
	switch ($("#convert-type").val()) {
		case "fromSrt": {
			fileReader.readAsText(fileContent);
			break;
		}
		case "fromKrc": {
			fileReader.readAsArrayBuffer(fileContent);
			break;
		}
		default:
			toast.fire({ icon: "error", text: "Unknown parameter" });
			break;
	}
});
$("#save-converted").on("click", function () {
	const lrcContent = $("#converted-lyric").text();
	blobDL(lrcContent, `converted.lrc`);
});
