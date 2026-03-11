<?php

namespace Modules\Tasks\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TasksController extends Controller
{
    public function index(Request $request)
    {
        return view('tasks::index');
    }
}
