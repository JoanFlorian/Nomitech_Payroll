<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Show the landing page with pricing plans
     */
    public function index(): View
    {
        // Get plans ordered by 'orden' field, with destacado plans first
        $planes = Plan::orderBy('destacado', 'desc')
            ->orderBy('orden', 'asc')
            ->get();

        return view('index', compact('planes'));
    }
}
