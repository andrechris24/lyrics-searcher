/* eslint-disable no-unused-vars */
/* global Swal, coreui */
$.ajaxSetup({ 
	timeout: 120000,
	beforeSend: function(){
		$(":input").removeClass('is-invalid');
	}
});
$(document).on("keydown", function (e) {
	// Ignore if user is already typing in an input, textarea, or contenteditable
	if (
		$(e.target).is('input, textarea, [contenteditable="true"]') ||
		$(".modal.show").length
	)
		return;

	// Check if the pressed key is forward slash "/"
	if (e.key === "/" || e.keyCode === 191) {
		e.preventDefault(); // Prevent browser's quick find (especially in Firefox)
		if ($("#track-name").length) $("#track-name").trigger("focus");
		else $('input[type="search"]').trigger("focus");
	}
}).on("ajaxComplete",function(){
	$("form :input").prop("disabled", false);
});
const toast = Swal.mixin({
		toast: true,
		position: "top-end",
		showConfirmButton: false,
		timer: 7000,
		timerProgressBar: true,
		theme: "bootstrap-5",
		didOpen: (toast) => {
			toast.onmouseenter = Swal.stopTimer;
			toast.onmouseleave = Swal.resumeTimer;
			toast.onclick = Swal.close;
		}
	}),
	swalConfirm = Swal.mixin({
		icon: "question",
		theme: "bootstrap-5",
		buttonsStyling: false,
		confirmButtonText: "Yes",
		showLoaderOnConfirm: true,
		showCancelButton: true,
		allowOutsideClick: !Swal.isLoading(),
		allowEscapeKey: !Swal.isLoading(),
	}),
	basicForm = "#basic-search-form",
	advancedForm = "#advanced-search-form";
$.LoadingOverlaySetup({
	background: "rgba(0, 0, 0, 0.5)",
	image: "",
	fontawesome: "fas fa-circle-notch fa-spin",
	fontawesomeColor: "#0dcaf0",
	text: "Loading...",
	textColor: "#0dcaf0",
	textResizeFactor: 0.2
});

function blobDL(data, filename, type = "text/plain") {
	const blob = new Blob([data], { type: type, charset: "utf-8" });
	let url = window.URL.createObjectURL(blob),
		a = document.createElement("a");
	a.href = url;
	a.download = filename;
	document.body.appendChild(a);
	a.click();
	a.remove();
	window.URL.revokeObjectURL(url);
}
const tooltipTriggerList = document.querySelectorAll(
	'[data-coreui-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
	(tooltipTriggerEl) => new coreui.Tooltip(tooltipTriggerEl)
);
function zpad(n) {
	const s = n.toString();
	return s.length < 2 ? `0${s}` : s;
}
function formatSeconds(s) {
	// Validate input
	if (typeof s !== "number" || isNaN(s) || s < 0) return "00:00"; // Default for invalid input

	// Calculate minutes and seconds
	const minutes = Math.floor(s / 60),
		seconds = s % 60;

	// Pad with leading zeros if needed
	const formattedMinutes = zpad(minutes),
		formattedSeconds = zpad(seconds);

	return `${formattedMinutes}:${formattedSeconds}`;
}
