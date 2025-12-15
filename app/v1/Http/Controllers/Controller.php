<?php

namespace V1\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use V1\Traits\Renderer;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, Renderer, ValidatesRequests;
}
