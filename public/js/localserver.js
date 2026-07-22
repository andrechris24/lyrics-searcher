/* global blobDL, toast, Swal, luxon, formatSeconds */
let dt_local;
const lyricsModal = document.getElementById("modalLocalFile");
$.fn.dataTable.ext.errMode = "none";
if (lyricsModal) {
	lyricsModal.addEventListener("show.coreui.modal", (e) => {
		const btn = e.relatedTarget;
		const songName = btn.getAttribute("data-coreui-title"),
			artistName = btn.getAttribute("data-coreui-artist"),
			albumName = btn.getAttribute("data-coreui-album"),
			duration = btn.getAttribute("data-coreui-duration"),
			content = btn.getAttribute("data-coreui-content"),
			user = btn.getAttribute("data-coreui-user"),
			upload = btn.getAttribute("data-coreui-upload"),
			offset = btn.getAttribute("data-coreui-offset");
		$("#local-song-title").text(songName);
		$("#local-song-artist").text(artistName);
		$("#local-song-album").text(albumName);
		$("#local-song-duration")
			.text(offset === 0 ? duration : `${duration} (${offset / 1000})`);
		$("#local-uploader").text(user);
		$("#local-song-upload").text(upload);
		$("#local-content").text(content);
	});
}
$(document)
	.ready(function () {
		dt_local = $("#local-lyrics")
			.DataTable({
				processing: true,
				responsive: true,
				serverSide: true,
				// stateSave: true,
				ajax: {
					url: "/local/data",
					method: "GET",
					headers: null,
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
					{ data: "title" },
					{ data: "artist" },
					{ data: "album" },
					{ data: "duration" },
					{ data: "user.name" },
					{ data: "created_at" },
					{ data: "content" }
				],
				order: [[5, "desc"]],
				columnDefs: [
					{
						target: 2,
						render: function(data){
							if(data===''||data===null) return '-';
							return data;
						}
					},
					{
						target: 3,
						searchable: false,
						render: function (data, type, full) {
							if (full["offset"] === 0) return formatSeconds(data);
							return `${formatSeconds(data)} (${full["offset"] > 0 ? "+" : ""}${full["offset"] / 1000})`;
						}
					},{
						target: 4,
						render: function(data){
							if(data===''||data===null) return 'Guest';
							return data;
						}
					},
					{
						target: 5,
						searchable: false,
						render: function (data) {
							return luxon.DateTime.fromISO(data).toRelative();
						}
					},
					{
						orderable: false,
						searchable: false,
						target: 6,
						render: function (data, type, full) {
							const create = luxon.DateTime.fromISO(
									full["created_at"],
								).toFormat("dd LLL yyyy HH:mm"),
								length = formatSeconds(full["duration"]);
							return (
								'<div class="btn-group btn-group-sm">' +
								'<button type="button" class="btn btn-info" data-coreui-toggle="modal"' +
								`data-coreui-target="#modalLocalFile" data-coreui-album="${full["album"] ?? "-"}"` +
								`data-coreui-duration="${length}" data-coreui-title="${full["title"]}"` +
								`data-coreui-artist="${full["artist"]}" data-coreui-content="${data}"` +
								`data-coreui-upload="${create}" data-coreui-offset="${full["offset"]}"` +
								`data-coreui-user="${full["user"] ? full["user"]["name"] : "Guest"}">` +
								'<i class="fa-solid fa-eye"></i></button>' +
								'<button type="button" class="btn btn-primary btn-sm dl-button"' +
								`data-album="${full["album"] ?? "-"}" data-title="${full["title"]}"` +
								`data-artist="${full["artist"]}" data-id="${full["id"]}"` +
								`data-duration="${length}" data-content="${data}"` +
								`data-user="${full["user"] ? full["user"]["name"] : "Guest"}"` +
								`data-offset="${full["offset"]}">` +
								'<i class="fa-solid fa-download"></i>' +
								"</button></div>"
							);
						}
					}
				]
			})
			.on("dt-error", function (e, settings, tn, message) {
				console.warn(message);
			});
	})
	.on("click", ".dl-button", function () {
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
						blobDL(`${fileName}\n\n${plainContent}`, fileName + ext);
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
				footer: typeof data.files !== "undefined" ? data.files.toString() : ""
			});
			dt_local.ajax.reload(null, true);
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
