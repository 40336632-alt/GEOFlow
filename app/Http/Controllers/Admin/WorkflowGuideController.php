<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class WorkflowGuideController extends Controller
{
    public function index()
    {
        return view('admin.workflow', [
            'pageTitle' => 'GEO内容发布流程',
            'activeMenu' => 'workflow',
        ]);
    }
}
