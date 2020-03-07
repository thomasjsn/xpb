<?php

namespace App;

use App\XpbContract;
use Illuminate\Support\Facades\Redis;

class ShortUrl extends Model implements XpbContract
{
    public $hash;
    public $content;
    public $type = 'short_url';


    function __construct()
    {
        //
    }


    public static function create(array $params)
    {
        $paste = new self();

        foreach($params as $key => $value) {
            $paste->$key = $value;
        }

        $paste->save();

        return $paste;
    }


    public static function find($hash)
    {
        $shortUrl = new self();
        $shortUrl->hash = $hash;

        $shortUrl->content = Redis::hget('sys:shorturl', $hash);

        if (is_null($shortUrl->content)) {
            return null;
        }

        return $shortUrl;
    }


    public function save()
    {
        $urlHash = Redis::hget('sys:chksum', md5($this->content));

        if (is_null($urlHash))
        {
            Redis::hset('sys:shorturl', $this->hash, $this->content);
            Redis::hset('sys:chksum', md5($this->content), $this->hash);

            Redis::sadd('sys:hashid', $this->hash);

            return true;
        } 
        else 
        {
            $this->hash = $urlHash;

            return false;
        }
    }


    public function delete(bool $release)
    {
        $url = Redis::hget('sys:shorturl', $this->hash);

        $result = [
            'content' => (bool)Redis::hdel('sys:shorturl', $this->hash),
            'chksum' => (bool)Redis::hdel('sys:chksum', md5($url)),
            'visits' => (bool)Redis::zrem('sys:visits', $this->hash),
            'hashid' => false
        ];

        if ($release) {
            $result['hashid'] = (bool)Redis::srem('sys:hashid', $this->hash);
        }
        
        // Only return keys that were actually deleted
        return array_filter($result, function ($var) { return $var; });
    }

}
