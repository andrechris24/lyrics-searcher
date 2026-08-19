/* global blobDL, toast, Swal, zpad, basicForm */
let songID, fileName, dt_lyrics, lyricContent;
const lyricsModal = document.getElementById("modalLyrics");
if (lyricsModal) {
	$.fn.dataTable.ext.errMode = "none";
	lyricsModal.addEventListener("show.coreui.modal", (event) => {
		// Extract info from data-coreui-* attributes
		songID = event.relatedTarget.getAttribute("data-coreui-hash");
		fileName = event.relatedTarget.getAttribute("data-coreui-query");
		$("#lrc-query").text(fileName);
		if ($.fn.dataTable.isDataTable("#lyrics-table")) dt_lyrics.destroy();

		// Update the modal's content
		dt_lyrics = $("#lyrics-table").DataTable({
			language: { emptyTable: "No lyrics available for this song" },
			lengthChange: false,
			processing: true,
			responsive: true,
			searching: false,
			ajax: {
				url: `/kugou/${songID}`,
				dataSrc: "",
				error: function (xhr, st, err) {
					toast.fire({
						icon: "error",
						text:
							st === "timeout"
								? "Connection timed out"
								: (xhr.responseJSON?.message ?? err ?? st)
					});
				}
			},
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
		});
	});
}
document.addEventListener("focusin", (e) => {
	if (e.target.closest('[class*="swal2-"]') !== null)
		e.stopImmediatePropagation(); //Prevent modal from stealing focus
});
$(basicForm).submit(function (event) {
	event.preventDefault();
	$("#kugou-loader").removeClass("d-none");
	$("#kugou-container").html("");
	sendAjax("/kugou/results", $(this).serialize());
});
$("#advanced-alt-search-form").submit(function (event) {
	event.preventDefault();
	$("#kugou-loader").removeClass("d-none");
	$("#kugou-container").html("");
	sendAjax("/kugou/advanced/results", $(this).serialize());
});
function sendAjax(url, data) {
	$("form :input").prop("disabled", true);
	$.ajax({
		url: url,
		data: data
	})
		.done(function (r) {
			$("#kugou-container").html(r.html);
		})
		.fail(function (xhr, st, err) {
			console.warn(err);
			try {
				if (xhr.status === 422) {
					if (typeof xhr.responseJSON.errors.title !== "undefined")
						$("#track-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.artist !== "undefined")
						$("#artist-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.query !== "undefined")
						$("#basic-search-query").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.minutes !== "undefined")
						$("#search-minutes").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.seconds !== "undefined")
						$("#search-seconds").addClass("is-invalid");
				}
				toast.fire({
					icon: "error",
					text:
						st === "timeout"
							? "Connection timed out"
							: (xhr.responseJSON?.message ?? "Server connection was lost")
				});
			} catch (e) {
				console.error(e);
				toast.fire({
					icon: "error",
					text: "An error occurred while marking input errors. Please report to developer."
				});
			}
		})
		.always(function () {
			$("#kugou-container").LoadingOverlay("hide");
			$("#kugou-loader").addClass("d-none");
		});
}
// eslint-disable-next-line no-unused-vars
function navigate(url, data) {
	$("#kugou-container").LoadingOverlay("show");
	sendAjax(url, data);
	$("html, body").animate(
		{ scrollTop: $("#kugou-container").offset().top },
		200
	);
}
// eslint-disable-next-line no-unused-vars
function dlLRC(id, key, file = null) {
	let ext;
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
					text: "To import lyrics to Aegisub, choose KRC Raw or Synced. For Word-by-Word lyrics, only a few players supported.",
					footer:
						'<a href="https://github.com/qwe7989199/Lyric-Importer-for-Aegisub">Additional script for Aegisub (Buggy for KRC)</a>',
					theme: "bootstrap-5",
					buttonsStyling: false,
					customClass: {
						confirmButton: "btn btn-primary btn-lg me-2",
						cancelButton: "btn btn-danger btn-lg"
					},
					topLayer: true,
					inputOptions: {
						wordbyword: "Word-by-Word",
						synced: "Synced",
						plain: "Plain",
						raw: "KRC Raw"
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
						blobDL(
							lyricContent,
							(file ?? fileName) + ext,
							ext === ".krc" ? "application/octet-stream" : undefined
						);
					}
				});
			} else {
				console.info(`Lyric type: ${data.format}`);
				blobDL(data.content, `${file ?? fileName}.lrc`);
			}
		},
		error: function (xhr, st, err) {
			console.warn(err);
			toast.fire({
				icon: "error",
				text:
					st === "timeout"
						? "Connection timed out"
						: (xhr.responseJSON?.message ?? "Server connection was lost")
			});
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
	const formattedMinutes = zpad(minutes);
	const formattedSeconds = zpad(seconds);

	return `${formattedMinutes}:${formattedSeconds}`;
}
