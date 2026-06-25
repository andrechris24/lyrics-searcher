/* global blobDL, toast, Swal, luxon, formatSeconds */
let dt_local;
const lyricsModal = document.getElementById("modalLocalFile");
$.fn.dataTable.ext.errMode = "none";
if (lyricsModal) {
	lyricsModal.addEventListener("show.bs.modal", (e) => {
		const btn = e.relatedTarget;
		const songName = btn.getAttribute("data-bs-title"),
			artistName = btn.getAttribute("data-bs-artist"),
			albumName = btn.getAttribute("data-bs-album"),
			duration = btn.getAttribute("data-bs-duration"),
			content = btn.getAttribute("data-bs-content"),
			user = btn.getAttribute("data-bs-user"),
			upload = btn.getAttribute("data-bs-upload"),
			update = btn.getAttribute("data-bs-update");
		$("#local-song-title").text(songName);
		$("#local-song-artist").text(artistName);
		$("#local-song-album").text(albumName);
		$("#local-song-duration").text(duration);
		$("#local-uploader").text(user);
		$("#local-song-upload").text(upload);
		$("#local-song-update").text(update);
		$("#local-content").text(content);
	});
}
$(document).ready(function () {
	dt_local = $("#local-lyrics")
		.DataTable({
			processing: true,
			responsive: true,
			serverSide: true,
			stateSave: true,
			ajax: "/local/data",
			columns: [
				{ data: "title" },
				{ data: "artist" },
				{ data: "album" },
				{ data: "duration" },
				{ data: "user.name" },
				{ data: "created_at" },
				{ data: "content" }
			],
			order: [[5, 'desc']],
			columnDefs: [
				{
					target: 3,
					searchable: false,
					render: function (data, type, full) {
						if (full["offset"] === 0) return formatSeconds(data);
						return `${formatSeconds(data)} (${full["offset"] > 0 ? "+" : ""}${full["offset"] / 1000})`;
					}
				},
				{
					target: 5,
					searchable: false,
					render: function (data) {
						return luxon.DateTime.fromISO(data).toFormat("dd LLL yyyy HH:mm");
					}
				},
				{
					orderable: false,
					searchable: false,
					target: 6,
					render: function (data, type, full) {
						const create = luxon.DateTime.fromISO(full["created_at"]).toFormat(
								"dd LLL yyyy HH:mm"
							),
							update = luxon.DateTime.fromISO(full["updated_at"]).toFormat(
								"dd LLL yyyy HH:mm"
							),
							length = formatSeconds(full["duration"]);
						return (
							'<div class="btn-group btn-group-sm">' +
							'<button type="button" class="btn btn-info" data-bs-toggle="modal"' +
							`data-bs-target="#modalLocalFile" data-bs-album="${full["album"]}"` +
							`data-bs-duration="${length}" data-bs-title="${full["title"]}"` +
							`data-bs-artist="${full["artist"]}"` +
							`data-bs-content="${data}"` +
							`data-bs-upload="${create}"` +
							`data-bs-user="${full["user"]["name"] ?? "Guest"}"` +
							`data-bs-offset="${full["offset"]}" data-bs-update="${update}">` +
							'<i class="fa-solid fa-eye"></i></button>' +
							'<button type="button" class="btn btn-primary btn-sm dl-button"' +
							`data-album="${full["album"]}" data-title="${full["title"]}"` +
							`data-artist="${full["artist"]}" data-id="${full["id"]}"` +
							`data-duration="${length}" data-content="${data}"` +
							`data-user="${full["user"]["name"] ?? "Guest"}" data-offset="${full["offset"]}">` +
							'<i class="fa-solid fa-download"></i>' +
							"</button></div>"
						);
					}
				}
			]
		})
		.on("dt-error", function (e, settings, tn, message) {
			toast.fire({ icon: "warning", text: message });
		});
}).on("click", ".dl-button", function () {
	let ext;
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
				title: "Download in Enhanced LRC format?",
				text: "This lyric contains syllable timestamps and only a few players supports this type.",
				theme: "bootstrap-5",
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: "Yes",
				denyButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) blobDL(meta + content, fileName + ext);
				else if (result.isDenied) {
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
			toast.fire({
				icon: data.status,
				text: data.message,
				footer: typeof data.files !== "undefined" ? data.files.toString() : "",
			});
			dt_local.ajax.reload(null, true);
			$("#uploadLyricForm")[0].reset();
		},
		error: function (xhr, st, err) {
			toast.fire({
				icon: "error",
				text: xhr.responseJSON?.message ?? err ?? st,
			});
		}
	});
});
