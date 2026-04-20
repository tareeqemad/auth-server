<?php

namespace App\Http\Controllers\OIDC;

use App\Http\Controllers\Controller;
use App\Services\OIDC\ClaimsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserInfoController extends Controller
{
    public function __invoke(Request $request, ClaimsService $claims): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $token = $user->token();
        $scopes = $token?->scopes ?? [];

        if (! in_array('openid', $scopes, true)) {
            return response()->json(['error' => 'insufficient_scope'], 403);
        }

        return response()->json($claims->getClaimsForUser($user, $scopes));
    }
}
