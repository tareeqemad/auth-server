<?php

namespace App\Http\Controllers\OIDC;

use App\Http\Controllers\Controller;
use App\Services\OIDC\JwksService;
use Illuminate\Http\JsonResponse;

class JwksController extends Controller
{
    public function __invoke(JwksService $service): JsonResponse
    {
        return response()->json($service->getJwks())
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
