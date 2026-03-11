<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CmsController extends Controller
{
    public function index(Request $request)
    {
        return view('cms::index');
    }
}
