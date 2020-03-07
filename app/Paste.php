<?php

namespace App;

use App\XpbContract;
use Illuminate\Support\Facades\Redis;

class Paste extends Model implements XpbContract
{
    public $hash;
    public $content;
    public $mime;
    public $ttl;
    public $type = 'paste';


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
        $paste = new self();
        $paste->hash = $hash;

        $meta = Redis::get($hash);

        if (file_exists($paste->fileName)) {
            $paste->content = file_get_contents($paste->fileName);
        }

        if (is_null($meta) || is_null($paste->content)) {
            return null;
        }

        $meta_json = json_decode($meta);
        $paste->mime = $meta_json->mime;
        $paste->ttl = $meta_json->ttl;

        return $paste;
    }


    public function save()
    {
        file_put_contents($this->fileName, $this->content);

        Redis::set($this->hash, json_encode([
            'mime' => $this->mime,
            'ttl' => $this->ttl
        ]));

        if ($this->ttl > 0) Redis::expire($this->hash, $this->ttl);

        Redis::sadd('sys:hashid', $this->hash);

        return true;
    }


    public static function cleanup($hash)
    {
        $paste = new self;
        $paste->hash = $hash;

        return $paste->delete(false);
    }


    public function delete(bool $release)
    {
        $result = [
            'file' => false,
            'meta' => (bool)Redis::del($this->hash),
            'visits' => (bool)Redis::zrem('sys:visits', $this->hash),
            'hashid' => false
        ];

        if (file_exists($this->fileName)) {
            $result['file'] = unlink($this->fileName);
        }

        if ($release) {
            $result['hashid'] = (bool)Redis::srem('sys:hashid', $this->hash);
        }
        
        // Only return keys that were actually deleted
        return array_filter($result, function ($var) { return $var; });
    }


    protected function getFileName()
    {
        $hash = str_replace('/', '!', $this->hash);

        return storage_path('app/'.$hash);
    }


    protected function getTimestamp()
    {
        return filectime($this->getFileName());
    }
}
