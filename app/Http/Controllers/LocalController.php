<?php

namespace App\Http\Controllers;

use App\Models\Lyric;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LocalController extends Controller
{
	public function standard(Request $request)
	{
		try {
			$request->validate(['title' => 'required', 'artist' => 'required']);
			$data = Lyric::whereLike('title', '%' . $request['title'] . '%')
				->whereLike('artist', '%' . $request['artist'] . '%')->paginate(20);
			return view('local.result', compact('data'));
		} catch (QueryException $e) {
			Log::error($e);
			return to_route('local.index')->withInput()
				->withError('Error retrieving search result: ' . $e->getMessage());
		} catch (ValidationException $e) {
			return to_route('local.index')->withInput()->withErrors($e->errors());
		}
	}
	public function advanced(Request $request)
	{
		try {
			$request->validate([
				'title' => 'nullable|required_without_all:artist,album|string',
				'artist' => 'nullable|required_without_all:title,album|string',
				'album' => 'nullable|required_without_all:title,artist|string'
			]);
			$model = new Lyric();
			if (!empty($request['title']))
				$model->whereLike('title', '%' . $request['title'] . '%');
			if (!empty($request['artist']))
				$model->whereLike('artist', '%' . $request['artist'] . '%');
			if (!empty($request['album']))
				$model->whereLike('album', '%' . $request['album'] . '%');
			$data = $model->paginate(20);
			return view('local.advanced.result', compact('data'));
		} catch (QueryException $e) {
			Log::error($e);
			return to_route('local.advanced')->withInput()
				->withError('Error retrieving search result: ' . $e->getMessage());
		} catch (ValidationException $e) {
			return to_route('local.advanced')->withInput()->withErrors($e->errors());
		}
	}
	public function aimp(int $id)
	{
		$data = Lyric::find($id);
		return response()->json($data);
	}
	public function latest()
	{
		try {
			$data = Lyric::latest()->limit(10)->get();
			return view('local.latest', compact('data'));
		} catch (QueryException $e) {
			Log::error($e);
			return to_route('local.index')
				->withError('Error retrieving latest uploads: ' . $e->getMessage());
		}
	}
}
