@extends('layout')
@section('title', 'YouTube Downloader')
@section('subpage-title', 'YouTube Downloader')
@section('content')
	<div class="px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5 mb-5 text-center">
		<ul class="nav nav-tabs" id="YoutubeDLFormTab" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="video-tab" data-coreui-toggle="tab"
					data-coreui-target="#video-tab-pane" type="button" role="tab"
					aria-controls="video-tab-pane" aria-selected="true">Video</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="audio-tab" data-coreui-toggle="tab"
					data-coreui-target="#audio-tab-pane" type="button" role="tab"
					aria-controls="audio-tab-pane" aria-selected="false">Audio</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="yt-alt-tab" data-coreui-toggle="tab"
					data-coreui-target="#yt-alt-tab-pane" type="button" role="tab"
					aria-controls="yt-alt-tab-pane" aria-selected="false">Alternative</button>
			</li>
		</ul>
		<div class="tab-content mt-3" id="YoutubeDLFormTabContent">
			<div class="tab-pane fade show active" id="video-tab-pane" role="tabpanel"
				aria-labelledby="video-tab" tabindex="0">
				<form action="#" class="row g-3" id="yt-video-form">
					<div class="row g-2">
						<div class="col-md-8">
							<div class="input-group input-group-lg">
								<span class="input-group-text">
									<i class="fa-solid fa-link"></i>
								</span>
								<div class="form-floating">
									<input type="url" name="url" placeholder="Enter YouTube URL here..."
										class="form-control form-control-lg" id="yt-video-url" required autofocus>
									<label for="yt-video-url">YouTube URL</label>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-floating">
								<select class="form-select" id="video-quality" name="q" required>
									<option value="">Select</option>
									<option value="1440">1440p</option>
									<option value="1080">1080p</option>
									<option value="720">720p</option>
									<option value="480">480p</option>
									<option value="360">360p</option>
								</select>
								<label for="video-quality">Quality</label>
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary">Download</button>
				</form>
			</div>
			<div class="tab-pane fade" id="audio-tab-pane" role="tabpanel"
				aria-labelledby="audio-tab" tabindex="0">
				<form action="#" class="row g-3" id="yt-audio-form">
					<div class="row g-2">
						<div class="col-md-8">
							<div class="input-group input-group-lg">
								<span class="input-group-text">
									<i class="fa-solid fa-link"></i>
								</span>
								<div class="form-floating">
									<input type="url" name="url" placeholder="Enter YouTube URL here..."
										class="form-control form-control-lg" id="yt-audio-url" required autofocus>
									<label for="yt-audio-url">YouTube URL</label>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-floating">
								<select class="form-select" id="audio-format" name="fmt" required>
									<option value="">Select</option>
									<option value="mp3">MP3</option>
									<option value="m4a">M4A</option>
									<option value="webm">WEBM</option>
									<option value="aac">AAC</option>
									<option value="flac">FLAC</option>
									<option value="opus">OPUS</option>
									<option value="ogg">OGG</option>
									<option value="wav">WAV</option>
								</select>
								<label for="audio-format">Format</label>
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary">Download</button>
				</form>
			</div>
			<div class="tab-pane fade" id="yt-alt-tab-pane" role="tabpanel"
				aria-labelledby="ytdlp-tab" tabindex="0">
				<form action="#" class="row g-3" id="yt-alt-form">
					<div class="row g-2">
						<div class="col-md-8">
							<div class="input-group input-group-lg">
								<span class="input-group-text">
									<i class="fa-solid fa-link"></i>
								</span>
								<div class="form-floating">
									<input type="url" name="url" placeholder="Enter YouTube URL here..."
										class="form-control form-control-lg" id="yt-alt-url" required autofocus>
									<label for="yt-alt-url">YouTube URL</label>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-floating">
								<select class="form-select" id="yt-alt-format" name="fmt" required>
									<option value="">Select</option>
									<option value="mp3">MP3</option>
									<option value="1080">1080p</option>
									<option value="720">720p</option>
									<option value="480">480p</option>
									<option value="360">360p</option>
									<option value="240">240p</option>
									<option value="144">144p</option>
								</select>
								<label for="yt-alt-format">Format</label>
							</div>
						</div>
					</div>
					<small class="form-text text-center">Powered by SaveTube</small>
					<button type="submit" class="btn btn-primary">Download</button>
				</form>
			</div>
		</div>
	</div>
	@include('youtube.placeholder')
	<div id="ytdl-container"></div>
@endsection
@section('js')
	<script type="text/javascript" src="{{ asset('js/ytdl.js') }}"></script>
@endsection
