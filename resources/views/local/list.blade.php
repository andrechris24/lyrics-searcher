<x-no-script />
@empty($data)
	<x-no-results source="local" />
@else
	@if (!request()->routeIs('local.latest'))
		<p class="text-center">Page {{ $data->currentPage() }} out of {{ $data->total() }}
			result(s), showing {{ $data->perPage() }} per page. Click on a list to download.</p>
	@else
		<p class="text-center">Showing latest 10 uploaded lyrics. Click on a list to download.</p>
	@endif
	<div class="list-group px-lg-5 mx-lg-5 px-md-3 mx-md-3 pb-5">
		@foreach ($data as $result)
			@php
				$length = gmdate('i:s', $result['duration']);
				if (preg_match('/<(\d+):(\d+).(\d+)>/', $result['content'])) {
					$type = 'Syllable';
					$color = 'success';
				} elseif (preg_match('/\[(\d+):(\d+).(\d+)\]/', $result['content'])) {
					$type = 'Synced';
					$color = 'primary';
				} else {
					$type = 'Plain';
					$color = 'secondary';
				}
			@endphp
			<a class="list-group-item list-group-item-action" href="#"
				data-album="{{ $result['album'] }}" data-duration="{{ $length }}"
				data-title="{{ $result['title'] }}" data-artist="{{ $result['artist'] }}"
				data-content="{{ $result['content'] }}" data-id="{{ $result['id'] }}"
				data-user="{{ $result->user->name ?? 'Guest' }}"
				data-offset="{{ $result['offset'] }}">
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1">{{ $result['title'] }}</h5>
					<small>
						{{ $length }} | by {{ $result->user->name ?? 'Guest' }}
						<span class="badge text-bg-{{ $color }}">{{ $type }}</span>
					</small>
				</div>
				<p class="mb-1">{{ $result['artist'] }}</p>
				<small>{{ $result['album'] }}</small>
			</a>
		@endforeach
	</div>
	@if (!request()->routeIs('local.latest'))
		{{ $data->links() }}
	@endif
@endif
