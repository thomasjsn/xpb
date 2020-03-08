<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Redis;

class Model
{
    public function __get($name)
    {
        $method = 'get' . \ucfirst($name);

        if(method_exists($this, $method)){
            return $this->$method();
        } else {
            return null;
        }
    }

    
    public function getLength()
    {
        return strlen($this->content);
    }

    public function getSize()
    {
        return Helper::formatBytes($this->getLength());
    }

    public function getUrl()
    {
        $url = sprintf('%s/%s', config('app.url'), $this->hash);

        $mimes = config('xpb.mimes');
        if (in_array($this->mime, array_keys($mimes))) {
            $url .= "." . $mimes[$this->mime];
        }

        return $url;
    }

    public function getHits()
    {
        return Redis::zscore('sys:visits', $this->hash);
    }

}