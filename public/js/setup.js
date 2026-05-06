$.ajaxSetup({
	timeout: 30000
});
// eslint-disable-next-line no-undef
const toast = Swal.mixin({
	toast: true,
	position: "top-end",
	showConfirmButton: false,
	timer: 5000,
	timerProgressBar: true,
	theme: "bootstrap-5",
	didOpen: (toast) => {
		toast.onmouseenter = Swal.stopTimer;
		toast.onmouseleave = Swal.resumeTimer;
	}
});
$.LoadingOverlaySetup({
	background: "rgba(0, 0, 0, 0.5)",
	image: "",
	fontawesome: "fas fa-circle-notch fa-spin",
	fontawesomeColor: "#ffffff"
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
