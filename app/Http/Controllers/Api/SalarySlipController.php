<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalarySlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalarySlipController extends Controller
{
    /**
     * Display a listing of the authenticated user's salary slips.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $slips = SalarySlip::with('user')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slips,
        ]);
    }
}
