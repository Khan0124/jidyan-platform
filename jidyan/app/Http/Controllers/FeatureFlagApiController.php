<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeatureFlagUpdateRequest;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;

class FeatureFlagApiController extends Controller
{
    public function index(): JsonResponse
    {
        $flags = FeatureFlag::query()
            ->orderBy('key')
            ->get()
            ->map(fn (FeatureFlag $flag) => $flag->only(['id', 'key', 'enabled', 'description', 'updated_at']));

        return response()->json(['data' => $flags]);
    }

    public function update(FeatureFlagUpdateRequest $request, FeatureFlag $featureFlag): JsonResponse
    {
        $featureFlag->update($request->validated());

        $featureFlag->refresh();

        return response()->json([
            'data' => $featureFlag->only(['id', 'key', 'enabled', 'description', 'updated_at']),
        ]);
    }
}
