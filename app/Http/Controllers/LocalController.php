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
			$request->validate(['query' => 'required']);
			$data = Lyric::whereAny(['title', 'artist', 'album'], 'like', '%' . $request['query'] . '%')
				->paginate(20);
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
			$request->validate(['title' => 'required']);
			$model = Lyric::whereLike('title', '%' . $request['title'] . '%');
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
	public function AIMP(int $id){
		// TODO: Get lyric by ID (Special function for AIMP)
	}
}
