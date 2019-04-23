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

        $content = trim(file_get_contents($file));

        // Make sure pasts are not too big
        if (strlen($content) > 400000 || strlen($content) == 0) {
            return response()->json([
                'status' => 'error',
                'error' => 400,
                'message' => 'Invalid paste length'
            ], 400);
        }

        Redis::set($hash, $content);
        Redis::expire($hash, 3600*24*90);

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


    public function stats()
    {
        $stats = [
            'pastes' => Redis::dbsize()
        ];

        $content = json_encode($stats, JSON_PRETTY_PRINT);
        $syntax = 'json';

        return response(view('paste', compact('content', 'syntax')));
    }


    public function show($hash, $syntax = null)
    {
        $content = Redis::get($hash);

        if (is_null($content)) abort(404);

        // Kick back expire, if paste is volatile
        if (Redis::ttl($hash) > -1) {
            Redis::expire($hash, 3600*24*90);
        }

        // Return plain text, if syntax says so
        if (in_array($syntax, ['raw', 'plain', 'text', 'nohighlight'])) {
            return response($content, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
        }

        // Redirect instead, if paste is valid URL
        if (filter_var(trim($content), FILTER_VALIDATE_URL)) {
            return redirect(trim($content));
        }

        return response(view('paste', compact('content', 'syntax')))
            ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
    }


}
