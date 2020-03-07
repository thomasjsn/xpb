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

        $mimeArray = explode("/", $this->mime);
        if (in_array($mimeArray[1] ?? null, ['jpeg', 'png', 'pdf'])) {
            $url .= "." . $mimeArray[1];
        }

        return $url;
    }

    public function getHits()
    {
        return Redis::zscore('sys:visits', $this->hash);
    }

}