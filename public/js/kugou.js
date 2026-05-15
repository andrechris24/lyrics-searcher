/* global blobDL, toast, Swal */
let songID, fileName, dt_lyrics, lyricContent;
const lyricsModal = document.getElementById("modalLyrics");
if (lyricsModal) {
	$.fn.dataTable.ext.errMode = "none";
	lyricsModal.addEventListener("show.bs.modal", (event) => {
		// Extract info from data-bs-* attributes
		songID = event.relatedTarget.getAttribute("data-bs-hash");
		fileName = event.relatedTarget.getAttribute("data-bs-query");
		$("#lrc-query").text(fileName);
		if ($.fn.dataTable.isDataTable("#lyrics-table")) dt_lyrics.destroy();

		// Update the modal's content
		dt_lyrics = $("#lyrics-table")
			.DataTable({
				language: { emptyTable: "No lyrics available for this song" },
				lengthChange: false,
				processing: true,
				responsive: true,
				searching: false,
				ajax: { url: `/kugou/${songID}`, dataSrc: "" },
				columns: [
					{ data: "singer" },
					{ data: "song" },
					{ data: "duration" },
					{ data: "id" }
				],
				columnDefs: [
					{
						target: 0,
						render: function (data) {
							return data.replaceAll("\u{3001}", ", ");
						}
					},
					{
						target: 2,
						render: function (data) {
							return formatMilliseconds(data);
						}
					},
					{
						orderable: false,
						target: 3,
						render: function (data, type, full) {
							const access = full["accesskey"];
							return (
								`<button type="button" class="btn btn-primary btn-sm dl-btn" onclick="dlLRC(${data},'${access}')">` +
								'<i class="fa-solid fa-download"></i>' +
								"</button>"
							);
						}
					}
				]
			})
			.on("dt-error", function (e, settings, tn, message) {
				toast.fire({ icon: "warning", text: message });
			});
	});
}
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});

function dlLRC(id, key) {
	let message, ext;
	$.ajax({
		url: `/kugou/get`,
		data: { id: id, key: key },
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if (data.format === "krc") {
				Swal.fire({
					title: "Choose lyric type to download",
					text: "To import lyric to Aegisub (requires additional script), choose KRC Raw or Synced. For Word-by-Word lyrics, only a few players supported.",
					footer:
						'<a href="https://github.com/qwe7989199/Lyric-Importer-for-Aegisub">Additional script for Aegisub</a>',
					theme: "bootstrap-5",
					buttonsStyling: false,
					customClass: {
						confirmButton: "btn btn-primary btn-lg me-2",
						cancelButton: "btn btn-danger btn-lg"
					},
					topLayer: true,
					inputOptions: {
						synced: "Synced",
						wordbyword: "Word-by-Word",
						raw: "KRC Raw",
						plain: "Plain"
					},
					input: "select",
					inputPlaceholder: "Select lyric type",
					showCancelButton: true,
					inputValidator: (value) => {
						return new Promise((resolve) => {
							if (!value) resolve("Please select lyric type to continue");
							else resolve();
						});
					}
				}).then((result) => {
					if (result.isConfirmed && result.value) {
						switch (result.value) {
							case "synced":
								lyricContent = data.content.replace(/<(\d+):(\d+).(\d+)>/g, "");
								ext = ".lrc";
								break;
							case "wordbyword":
								lyricContent = data.content;
								ext = ".lrc";
								break;
							case "raw":
								lyricContent = Uint8Array.fromBase64(data.raw);
								ext = ".krc";
								break;
							default: //plain or unknown
								lyricContent = data.content
									.replace(/<(\d+):(\d+).(\d+)>/g, "")
									.replace(/\[(\d+):(\d+).(\d+)\]/g, "");
								ext = ".txt";
								break;
						}
						blobDL(lyricContent, fileName + ext);
					}
				});
			} else {
				console.info(`Lyric type: ${data.format}`);
				blobDL(data.content, `${fileName}.lrc`);
			}
		},
		error: function (xhr, st) {
			console.warn(xhr.responseJSON?.message ?? st);
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON?.message ?? st;
			toast.fire({ icon: "error", text: message });
		}
	});
}
function formatMilliseconds(ms) {
	// Validate input
	if (typeof ms !== "number" || isNaN(ms) || ms < 0) return "00:00"; // Default for invalid input

	// Convert to total seconds
	const totalSeconds = Math.floor(ms / 1000);

	// Calculate minutes and seconds
	const minutes = Math.floor(totalSeconds / 60);
	const seconds = totalSeconds % 60;

	// Pad with leading zeros if needed
	const formattedMinutes = String(minutes).padStart(2, "0");
	const formattedSeconds = String(seconds).padStart(2, "0");

	return `${formattedMinutes}:${formattedSeconds}`;
}
