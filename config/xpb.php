<?php

return [

    'limits' => [
        'min_age' => 3600*24*7,
        'max_age' => 3600*24*180,
        'max_size' => 1024*1024*8
    ],

    'keys' => [
        'sys:apikey',
        'sys:hashid',
        'sys:visits',
        'sys:traffic',
        'sys:shorturl',
        'sys:chksum'
    ]
    
];