<?php
namespace App\Controllers;

class IndexController
{
    public function index()
    {
        return response('Hello World');
    }
}