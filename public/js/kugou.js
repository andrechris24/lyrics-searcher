let songID,fileName,dt_lyrics;
const lyricsModal = document.getElementById("modalLyrics");
if (lyricsModal) {
	lyricsModal.addEventListener("shown.bs.modal", (event) => {
		const button = event.relatedTarget;

		// Extract info from data-bs-* attributes
		songID = button.getAttribute("data-bs-hash");
		fileName = button.getAttribute("data-bs-query");

		// If necessary, you could initiate an Ajax request here
		// and then do the updating in a callback

		// Update the modal's content
		$("#lrc-query").text(fileName);
		if($.fn.dataTable.isDataTable('#lyrics-table')) dt_lyrics.destroy();
		dt_lyrics=$("#lyrics-table").DataTable({
				lengthChange: false,
				processing: true,
				responsive: true,
				searching: false,
				ajax: {url: `/kugou/${songID}`, dataSrc: ''},
				columns: [
					{ data: "singer" },
					{ data: "song" },
					{ data: "duration" },
					{ data: "id" },
				],
				columnDefs: [
					{
						target: 2,
						render: function(data){
							return formatMilliseconds(data);
						}
					},{
						orderable: false,
						target: 3,
						render: function (data, type, full) {
							const access=full['accesskey'];
							return (
								`<button type="button" class="btn btn-primary btn-sm" onclick="download(${data},'${access}')">` +
								'<i class="fa-solid fa-download"></i>' +
								"</button>"
							);
						}
					}
				]
			}).on("dt-error", function (e, settings, tn, message) {
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
		url: `/kugou/${songID}`,
		headers: { "X-CSRF-TOKEN": csrfToken },
		method: "POST",
		data: { id: id, key: key },
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if(data.fmt==='krc')
				console.info('Downloading in Enhanced LRC format');
			blobDL(data.content, `${fileName}.lrc`);
		},
		error: function (xhr, st) {
			if (st === "timeout") message = "Connection timed out";
			else message = xhr.responseJSON.message ?? st;
			toast.fire({ icon: "error", text: message });
		},
	});
};
function formatMilliseconds(ms) {
    // Validate input
    if (typeof ms !== "number" || isNaN(ms) || ms < 0) {
        return "00:00"; // Default for invalid input
    }

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