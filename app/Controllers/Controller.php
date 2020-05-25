<?php

namespace App\Controllers;

use Core\Http\Request;

class Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function error($msg = '', $code = 500, $data = [])
    {
        return response(['code' => $code, 'data' => $data, 'msg' => $msg ?: 'server error']);
    }
}
