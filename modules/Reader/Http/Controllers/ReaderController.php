<?php

namespace Modules\Reader\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReaderController extends Controller
{
    public function index(Request $request)
    {
        return view('reader::index');
    }
}
