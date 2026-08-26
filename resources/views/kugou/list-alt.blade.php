@if (count($data) > 0)
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 mb-5 pb-5">
		@foreach ($data as $result)
			<a class="list-group-item list-group-item-action" href="javascript:"
				onclick="dlLRC({{ $result['id'] }},'{{ $result['accesskey'] }}','{{ $result['singer'] . ' - ' . $result['song'] }}');">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['song'] }}</h5>
				</div>
				<p class="mb-1">{{ $result['singer'] }}</p>
				<small>{{ gmdate('i:s', $result['duration'] / 1000) }}</small>
			</a>
		@endforeach
	</div>
@else
	<div class="alert alert-warning" role="alert">
		There are no lyrics available for your search query. Try with different casing.
	</div>
@endif
