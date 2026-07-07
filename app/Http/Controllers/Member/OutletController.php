<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Inertia\Response;

class OutletController extends Controller
{
    public function index(): Response
    {
        return inertia('member/outlets/Index', [
            'outlets' => Outlet::with('kasir')
                ->where('is_active', true)
                ->get(),
        ]);
    }
}
