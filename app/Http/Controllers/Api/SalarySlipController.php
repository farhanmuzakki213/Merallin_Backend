<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalarySlipController extends Controller
{
    /**
     * Display a listing of the authenticated user's salary slips.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $slips = $user->salarySlips()
                      ->latest()
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $slips,
        ]);
    }
}
