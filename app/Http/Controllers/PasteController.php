<?php

namespace App\Http\Controllers;

use App\Paste;
use App\ShortUrl;
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


    public function store(Request $request)
    {
        if (! Redis::hexists('sys:apikey', $request->header('X-API-Key'))) {
            abort(403, 'Incorrect or missing API key');
        }

        $file = $request->file('file');
        $content = trim(file_get_contents($file));

        $hlen = $request->get('hlen') ?? 6;
        $hash = $request->get('hash') ?? $this->getNewHash($hlen);

        if ($request->has('prefix')) {
            $hash = $request->get('prefix') . '/' . $hash;
        }

        // Limits
        $min_age = config('xpb.limits.min_age');
        $max_age = config('xpb.limits.max_age');
        $max_size = config('xpb.limits.max_size');

        // Make sure pasts are not too big
        if (strlen($content) > $max_size || strlen($content) == 0) {
            abort(400, 'Invalid paste length');
        }

        // Check hash is valid
        if (! $this->isValidHash($hash)) {
            abort(422, 'Hash contains invalid character(s)');
        }

        // Make sure hash is available
        if (Redis::exists($hash) || Redis::sismember('sys:hashid', $hash)) {
            abort(409, 'Hash already exists');
        }

        $is_link = (bool)filter_var(trim($content), FILTER_VALIDATE_URL);

        try {
            if ($is_link) {
                $paste = ShortUrl::create([
                    'hash' => $hash,
                    'content' => $content
                ]);
            }
            else {
                // From https://github.com/lachs0r/0x0
                $retention = $min_age + (-$max_age + $min_age) * pow((strlen($content) / $max_size - 1), 3);

                $paste = Paste::create([
                    'hash' => $hash,
                    'content' => $content,
                    'mime' => $request->get('mime'),
                    'ttl' => $request->get('ttl') ?? round($retention, 0)
                ]);
            }
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

		$response = [
            'status' => 'ok',
            'type' => $paste->type,
            'length' => $paste->length,
            'size' =>  $paste->size,
            'mime' => $paste->mime ?? 'text/plain',
            'ttl' => ! is_null($paste->ttl) ? $paste->ttl->diffInSeconds() : null,
            'retention' => ! is_null($paste->ttl) ? $paste->retention->diffInDays() : null,
            'url' => $paste->url
        ];

        \Log::info('Paste created', ['paste' => $paste->hash]);

        return response()->json($response, 201);
    }


    public function index(Request $request)
    {
        return $this->show($request, 'home');
    }


    public function show(Request $request, $hash)
    {
        $hash = explode(".", $hash)[0];

        // Use the first query string variable as syntax
        $query = array_keys($request->all());
        $syntax = $query[0] ?? null;

        // Get paste and short URL
        $paste = Paste::find($hash);
        $shortUrl = ShortUrl::find($hash);

        // If short URL, then redirect
        if (! is_null($shortUrl)) {
            Redis::zincrby('sys:visits', 1, $hash);

            return redirect($shortUrl->content);
        }

        if (is_null($paste)) {
            abort(404, 'Invalid hash key, no content found');
        }

        // Store some stats
        Redis::zincrby('sys:visits', 1, $hash);
        Redis::zincrby('sys:traffic', $paste->length, date('Y-m'));

        // Kick back expire, if paste is volatile
        if (Redis::ttl($hash) > -1) {
            Redis::expire($hash, $paste->ttl);
        }

        if (! is_null($paste->mime)) {
            return response($paste->content, 200)
                ->header('Content-Type', $paste->mime)
                ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
        }
        else if (in_array($syntax, ['raw', 'plain', 'text'])) {
            return response($paste->content, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
        }

        return response(view('paste', ['content' => $paste->content, 'syntax' => $syntax]))
            ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
    }

}
