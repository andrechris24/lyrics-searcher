<?php

namespace App\Http\Controllers;

use App\Models\Lyric;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\{File, Log};
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LocalController extends Controller
{
	public function list()
	{
		return DataTables::eloquent(Lyric::with('user'))->toJson();
	}
	public function aimp(Request $req)
	{
		$req->validate(['title' => 'required', 'artist' => 'required']);
		$data = Lyric::whereLike('title', '%' . $req['title'] . '%')
			->whereLike('artist', '%' . $req['artist'] . '%')->limit(5)->get();
		return response()->json($data);
	}
	public function get(int $id)
	{
		$data = Lyric::find($id);
		return response()->json($data);
	}
	public function upload(Request $req)
	{
		$req->validate(
			['lrc.*' => 'required|file|extensions:lrc,elrc,txt|max:2048|encoding:UTF-8']
		);
		$failed = 0;
		$total = count($req->file('lrc'));
		$files = [];
		foreach ($req->file('lrc') as $file) {
			try {
				$path = $file->store('files');
				$absolutePath = storage_path('app/private/' . $path);
				$lines = File::lines($absolutePath);
				$metaRegex = "/^\[(ti|ar|al|offset|au|by|length|ve|re|id|lr|tool):([^\]]+)\]$/i";
				$fileRegex = "/^(.+?)\s*-\s*(.+)$/u";
				$lrcLines = '';
				$queries = [];
				foreach ($lines as $line) {
					if (preg_match($metaRegex, $line, $matches)) { // meta info
						switch ($matches[1]) {
							case 'ar':
								$queries['artist'] = Str::trim($matches[2]);
								break;
							case 'ti':
								$queries['title'] = Str::trim($matches[2]);
								break;
							case 'al':
								$queries['album'] = Str::trim($matches[2]);
								break;
							case 'offset':
								$queries['offset'] = (int)Str::trim($matches[2]);
								break;
							case 'length':
								$duration = explode(':', Str::trim($matches[2]));
								$queries['duration'] =
									['minutes' => $duration[0], 'seconds' => $duration[1]];
								break;
							default:
								break;
						}
						continue;
					}
					$lrcLines .= $line . "\n";
				}
				$fullname = $file->getClientOriginalName();
				$filename = File::name($fullname);
				if (!array_key_exists('title', $queries)) {
					if (preg_match($fileRegex, $filename, $fileMatch)) {
						$queries['title'] = $fileMatch[2];
						$queries['artist'] = $fileMatch[1];
						if (!array_key_exists('album', $queries))
							$queries['album'] = $fileMatch[2]; //Match album name as Title if empty
					} else {
						$queries['title'] = $filename;
						$queries['artist'] = 'Unknown artist';
					}
				} else if (!array_key_exists('artist', $queries)) {
					$queries['artist'] =
						preg_match($fileRegex, $filename, $fileMatch)
						? $fileMatch[1] : 'Unknown artist';
				}
				if (array_key_exists('title', $queries) && !array_key_exists('album', $queries))
					$queries['album'] = $queries['title'];
				File::delete($absolutePath);
				$queries['user_id'] = backpack_user()->id;
				$queries['content'] = $lrcLines;
				Lyric::create($queries);
			} catch (QueryException $e) {
				if ($file->getClientOriginalName())
					$files[] = $file->getClientOriginalName();
				Log::error($e);
				$failed++;
			} catch (\Exception $e) {
				if ($file->getClientOriginalName())
					$files[] = $file->getClientOriginalName();
				Log::error($e);
				$failed++;
			}
		}
		if ($failed >= $total) {
			Log::warning('Failed to upload lyrics: ', $files);
			$message = $total === 1 ?
				'File failed to upload' :
				'All ' . $total . ' files failed to upload';
			return response()->json(['message' => $message], 500);
		} else if ($failed > 0) {
			Log::warning('Failed to upload lyrics: ', $files);
			return response()->json([
				'status' => 'warning',
				'message' => $failed . ' out of ' . $total . ' files failed to upload',
				'files' => $files
			]);
		}
		$message = $total === 1 ?
			'File uploaded successfully' :
			'All ' . $total . ' files uploaded successfully';
		return response()->json(['status' => 'success', 'message' => $message]);
	}
}
