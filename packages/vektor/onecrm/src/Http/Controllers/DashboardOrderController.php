<?php

namespace Vektor\OneCRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardOrderController extends Controller
{
    public function index(Request $request)
    {
        return view('onecrm::dashboard.orders.index');
    }

    public function show(Request $request, $id)
    {
        return view('onecrm::dashboard.orders.show', ['id' => $id]);
    }
}
