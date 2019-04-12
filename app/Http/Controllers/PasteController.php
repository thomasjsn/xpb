<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PasteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function create(Request $request)
    {
        $file = $request->file('file');
        $hash = $this->getNewHash();

        $content = file_get_contents($file);

        // Make sure pasts are not too big
        if (strlen($content) > 400000) {
            return response()->json([
                'status' => 'error',
                'error' => 413,
                'message' => 'Too long'
            ], 413);
        }

        Redis::set($hash, $content);
        Redis::expire($hash, 31536000);

		$response = [
            'status' => 'ok',
            'message' => 'Paste successfully created',
            'length' => strlen($content),
            'url' => config('app.url') . '/' . $hash
        ];

        return response()->json($response, 201);
    }


    public function index()
    {
        $content = Redis::get('about');
        $syntax = 'md';

        return response(view('paste', compact('content', 'syntax')));
    }


    public function show($hash, $syntax = null)
    {
        $content = Redis::get($hash);

        if (is_null($content)) abort(404);

        // Dont expire "protected" pastes
        if (! in_array($hash, ['about', 'syntax'])) {
            Redis::expire($hash, 31536000);
        }

        // Redirect instead if paste is valid URL
        if (filter_var(trim($content), FILTER_VALIDATE_URL)) {
            return redirect(trim($content));
        }

        return response(view('paste', compact('content', 'syntax')))
            ->header('Cache-Control', 'public, max-age=604800');
    }


}
