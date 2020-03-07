<?php

namespace App;

interface XpbContract
{
    static public function create(array $params);

    public static function find($hash);

    public function save();

    public function delete(bool $release);
}