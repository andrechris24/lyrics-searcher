/* eslint-disable no-unused-vars */
/* global Swal, bootstrap */
$.ajaxSetup({ timeout: 60000 });
$(document).on("keydown", function (e) {
	// Ignore if user is already typing in an input, textarea, or contenteditable
	if ($(e.target).is('input, textarea, [contenteditable="true"]')) 
		return;

	// Check if the pressed key is forward slash "/"
	if (e.key === "/" || e.keyCode === 191) {
		e.preventDefault(); // Prevent browser's quick find (especially in Firefox)
		if ($("#track-name").length) $("#track-name").focus();
		else $('input[type="search"]').focus();
	}
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
});
const swalConfirm = Swal.mixin({
	icon: "question",
	theme: "bootstrap-5",
	buttonsStyling: false,
	confirmButtonText: "Yes",
	showLoaderOnConfirm: true,
	showCancelButton: true,
	allowOutsideClick: !Swal.isLoading(),
	allowEscapeKey: !Swal.isLoading()
});
$.LoadingOverlaySetup({
	background: "rgba(0, 0, 0, 0.5)",
	image: "",
	fontawesome: "fas fa-circle-notch fa-spin",
	fontawesomeColor: "#0d6efd",
	text: "Loading...",
	textColor: "#0d6efd",
	textResizeFactor: 0.2
});

function blobDL(data, filename) {
	const blob = new Blob([data], {
		type: "text/plain",
		charset: "utf-8"
	});
	let url = window.URL.createObjectURL(blob),
		a = document.createElement("a");
	a.href = url;
	a.download = filename;
	document.body.appendChild(a);
	a.click();
	a.remove();
	window.URL.revokeObjectURL(url);
}
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
