<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{

	protected function getNewHash($length = 6)
    {
        $permitted_chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

		while(1)
		{
            $hash = $this->generateString($permitted_chars, $length);
            if (! Redis::exists($hash) && ! Redis::sismember('meta:hashid', $hash)) return $hash;
			$length++;
		}
	}


    private function generateString($input, $strength)
    {
		$input_length = mb_strlen($input, '8bit');
        $random_string = '';

		for($i = 0; $i < $strength; $i++) {
			$random_string .= $input[mt_rand(0, $input_length - 1)];
		}

		return $random_string;
    }

}
