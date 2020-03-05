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
        $content = trim(file_get_contents($file));

        $is_link = (bool)filter_var(trim($content), FILTER_VALIDATE_URL);

        $min_age = 3600*24*7;
        $max_age = 3600*24*180;
        $max_size = 1024*1024*8;

        // From https://github.com/lachs0r/0x0
        $retention = $min_age + (-$max_age + $min_age) * pow((strlen($content) / $max_size - 1), 3);

        $mime = $request->get('mime');
        $ttl = $request->get('ttl') ?? round($retention, 0);
        $hlen = $request->get('hlen') ?? 6;
        $hash = $request->get('hash') ?? $this->getNewHash($hlen);

        // Make sure hash is available
        if (Redis::exists($hash) || Redis::sismember('meta:hashid', $hash)) {
            return response()->json([
                'status' => 'error',
                'error' => 409,
                'message' => 'Hash already exists'
            ], 409);
        }

        // Make sure pasts are not too big
        if (strlen($content) > $max_size || strlen($content) == 0) {
            return response()->json([
                'status' => 'error',
                'error' => 400,
                'message' => 'Invalid paste length'
            ], 400);
        }

        if (! $is_link) {
            file_put_contents(storage_path('app/'.$hash), $content);

            Redis::set($hash, json_encode([
                'mime' => $mime,
                'ttl' => $ttl
            ]));
            if ($ttl > 0) Redis::expire($hash, $ttl);
            $status = ['ok', 'Paste successfully created'];
        }
        else {
            $urlHash = Redis::hget('urls:chksum', md5($content));

            if (is_null($urlHash)) {
                Redis::hset('urls:hashid', $hash, $content);
                Redis::hset('urls:chksum', md5($content), $hash);
                $status = ['ok', 'Link successfully created'];
            } else {
                $hash = $urlHash;
                $status = ['found', 'Link found, returning existing data'];
            }
        }

        // Store the hash ID so it can not be used again
        Redis::sadd('meta:hashid', $hash);

        $url = sprintf('https://%s/%s', $request->getHost(), $hash);

        $mimeArray = explode("/", $mime);
        if (in_array($mimeArray[1] ?? null, ['jpeg', 'png', 'pdf'])) {
            $url .= "." . $mimeArray[1];
        }

		$response = [
            'status' => $status[0],
            'message' => $status[1],
            'length' => strlen($content),
            'size' => $this->formatBytes(strlen($content)),
            'mime' => $mime ?? 'text/plain',
            'ttl' => Redis::ttl($hash),
            'ttl_d' => round(Redis::ttl($hash) / (3600*24), 1),
            'is_link' => $is_link,
            'url' => $url
        ];

        \Log::info('Paste successfully created', ['paste' => $hash]);

        return response()->json($response, 201);
    }


    public function index(Request $request)
    {
        return $this->show($request, 'home');
    }


    public function stats()
    {
        $dates = [
            date('Y-m', strtotime('0 month')),
            date('Y-m', strtotime('-1 month')),
            date('Y-m', strtotime('-2 month')),
        ];

        $traffic = [];

        foreach($dates as $date) {
            $traffic[$date] = $this->formatBytes(Redis::zscore('meta:traffic', $date));
        }

        $stats = [
            'paste_count' => Redis::dbsize() > 5 ? Redis::dbsize() - 5 : 0,
            'link_count' => Redis::hlen('urls:hashid'),
            'used_keys' => Redis::scard('meta:hashid'),
            'traffic' => $traffic
        ];

        $content = json_encode($stats, JSON_PRETTY_PRINT);
        $syntax = 'json';

        return response(view('paste', compact('content', 'syntax')));
    }


    public function show(Request $request, $hash, $syntax = null)
    {
        $hash = explode(".", $hash)[0];

        $meta = Redis::get($hash);
        $link = Redis::hget('urls:hashid', $hash);
        if (is_null($meta) && is_null($link)) abort(404);

        // If hash key is URL, then redirect
        if (! is_null($link)) {
            Redis::zincrby('urls:visits', 1, $hash);

            return redirect($link);
        }

        // URL only domains stop here
        $urlDomains = explode(' ', env('URL_DOMAINS'));
        if (in_array($request->getHost(), $urlDomains)) abort(404);

        // If the hash key doesn't have a file, delete it and return 404
        if (! file_exists(storage_path('app/'.$hash))) {
            Redis::del($hash);
            abort(404);
        }

        $content = file_get_contents(storage_path('app/'.$hash));
        $meta_json = json_decode($meta);
        $mime = $meta_json->mime ?? null;
        $ttl = $meta_json->ttl ?? 3600*24*180;

        // Store some stats
        Redis::zincrby('meta:visits', 1, $hash);
        Redis::zincrby('meta:traffic', strlen($content), date('Y-m'));

        // Kick back expire, if paste is volatile
        if (Redis::ttl($hash) > -1) {
            Redis::expire($hash, $ttl);
        }

        // Return plain text, if syntax says so
        if (in_array($syntax, ['raw', 'plain', 'text', 'nohighlight'])) {
            return response($content, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
        }
        else if (! is_null($mime)) {
            return response($content, 200)
                ->header('Content-Type', $mime)
                ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
        }

        return response(view('paste', compact('content', 'syntax')))
            ->header('Cache-Control', 'public, max-age=' . 3600*24*7);
    }

}
