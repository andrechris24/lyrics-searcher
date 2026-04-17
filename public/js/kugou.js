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
							return data.replace("\u{3001}", ", ");
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
							// console.log(full['krctype']);
							return (
								`<button type="button" class="btn btn-primary btn-sm" onclick="download(${data},'${access}')">` +
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

function download(id, key) {
	const csrfToken = document
		.querySelector('meta[name="csrf-token"]')
		.getAttribute("content");

	let message;
	$.ajax({
		url: `/kugou/get`,
		headers: { "X-CSRF-TOKEN": csrfToken },
		method: "POST",
		data: { id: id, key: key },
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: async function (data) {
			try {
				if (data.format === "krc") {
					const choice = await Swal.fire({
						title: "Choose lyric type to download",
						text: "Note: only a few players support word-by-word lyrics.",
						theme: "bootstrap-5",
						showDenyButton: true,
						showCancelButton: true,
						confirmButtonText: "Synced",
						denyButtonText: "Word-by-Word",
					});
					if (choice.isConfirmed)
						lyricContent = data.content.replace(/<\d{2}:\d{2}\.\d{2}>/g, "");
					else if (choice.isDenied) lyricContent = data.content;
					else if (choice.isDismissed) return false;
					else throw "Unknown choice";
				} else lyricContent = data.content;
				blobDL(lyricContent, `${fileName}.lrc`);
			} catch (e) {
				console.warn(e);
			}
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
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
