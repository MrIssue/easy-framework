<?php

namespace App\Jobs;


abstract class Job
{
    public $tries = 1;

    abstract function handler() : bool;
}
