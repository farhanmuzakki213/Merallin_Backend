<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserProfileController extends Controller
{
    /**
     * Serve the user's ID card file securely.
     */
    public function serveIdCard(Request $request, string $uuid): StreamedResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        // Security check: Only the authenticated user can download their own ID card.
        if ($request->user()->id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user->id_card_path || !Storage::disk('public')->exists($user->id_card_path)) {
            abort(404, 'ID Card file not found.');
        }

        return Storage::disk('public')->download($user->id_card_path);
    }
}
