/* global basicForm, advancedForm, toast */
$(basicForm).submit(function (event) {
	event.preventDefault();
	sendAjax("/lrcred/results", $(basicForm).serialize());
});
$(advancedForm).submit(function (event) {
	event.preventDefault();
	sendAjax("/lrcred/advanced/results", $(advancedForm).serialize());
});
function sendAjax(url, data) {
	$("#lrcred-container").html("");
	$("form :input").prop("disabled", true);
	$("#lrcred-loader").removeClass("d-none");
	$.ajax({
		url: url,
		data: data
	})
		.done(function (r) {
			$("#lrcred-container").html(r.html);
			const allTooltip = document.querySelectorAll(
				'[data-coreui-toggle="tooltip"]'
			);
			// eslint-disable-next-line no-unused-vars
			const ttList = [...allTooltip].map(
				(tooltipTriggerEl) => new coreui.Tooltip(tooltipTriggerEl)
			);
		})
		.fail(function (xhr, st, err) {
			console.warn(`${st}: ${err}`);
			try {
				if (xhr.status === 422) {
					if (typeof xhr.responseJSON.errors.title !== "undefined")
						$("#track-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.artist !== "undefined")
						$("#artist-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.album !== "undefined")
						$("#album-name").addClass("is-invalid");
					if (typeof xhr.responseJSON.errors.query !== "undefined")
						$("#basic-search-query").addClass("is-invalid");
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
			$("#lrcred-loader").addClass("d-none");
		});
}
