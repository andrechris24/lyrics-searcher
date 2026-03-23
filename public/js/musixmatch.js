function blobDL(data, filename){
	const blob = new Blob([data],{type: 'text/plain', charset: 'utf-8'});
	let url = window.URL.createObjectURL(blob);
	let a = document.createElement("a");
	a.href = url;
	a.download = filename;
	document.body.appendChild(a);
	a.click();
	a.remove();
	window.URL.revokeObjectURL(url);
}
$(".download-btn").on('click',function(e){
	e.preventDefault();
	const id=$(this).data('id'), type=$(this).data('type'),
		artist=$(this).data('artist'), title=$(this).data('title'), album=$(this).data('album');
	const fileName=`${artist} - ${title}`;
	let contents, ext;
	$.ajax({
		method: "GET",
		url: `/musixmatch/${id}/${type}`,
		beforeSend: function () {
			$.LoadingOverlay("show");
		},
		complete: function () {
			$.LoadingOverlay("hide");
		},
		success: function (data) {
			if(type==='lyrics') {
				contents=fileName;
				ext='txt';
			}	else {
				contents=`[ar: ${artist}]\n[ti: ${title}]\n`+
					`[al: ${album}]\n[by: Musixmatch]\n[length: ${data.duration}]\n`;
				ext='lrc';
			}
			blobDL(contents + data.content,`${fileName}.${ext}`);
		},
		error: function (xhr, st) {
			toast.fire({icon: 'error',text: xhr.responseJSON.message ?? st});
		}
	});
});