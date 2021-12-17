<?php

return [

    'limits' => [
        'min_age' => 3600*24*7,     // days
        'max_age' => 3600*24*180,   // days
        'max_size' => 1024*1024*64  // MB
    ],

    'cache' => [
        'max-age' => 3600*24*7
    ],

    'keys' => [
        // HASH : API keys with comments
        'sys:apikey',

        // SET : expired and current keys, remove when released
        'sys:hashid',

        // SORTED SET : hits for pastes and short urls
        'sys:visits',

        // SORTED SET : traffic pr month by pastes
        'sys:traffic',

        // HASH : short url keys and locations
        'sys:shorturl',

        // HASH : content checksum and corresponding key
        'sys:chksum'
    ],

    'mimes' => [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'text/css' => 'css',
        'text/javascript' => 'js',
        'application/json' => 'json',
        'application/pdf' => 'pdf',
        'video/mp4' => 'mp4'
    ],

    'errors' => [
        400 => 'Bad request',
        403 => 'Forbidden',
        404 => 'Not found',
        409 => 'Conflict',
        422 => 'Unprocessable entity'
    ]
    
];
