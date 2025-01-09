<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserPreference\StoreUserPreferenceRequest;
use App\Http\Requests\UserPreference\BulkUpdateUserPreferenceRequest;
use App\Models\UserPreference;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPreferenceController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $preferences = auth()->user()->preferences()
            ->with(['category', 'source'])
            ->ordered()
            ->get();

        return response()->json([
            'data' => $preferences,
        ]);
    }

    public function store(StoreUserPreferenceRequest $request): JsonResponse
    {
        $preference = auth()->user()->preferences()->create([
            'category_id' => $request->category_id,
            'source_id' => $request->source_id,
            'preference_type' => $request->preference_type,
            'priority' => $request->priority ?? 0,
        ]);

        return response()->json([
            'message' => 'Preference created successfully',
            'data' => $preference->load(['category', 'source']),
        ], 201);
    }

    public function destroy(UserPreference $preference): JsonResponse
    {
        $this->authorize('delete', $preference);
        $preference->delete();

        return response()->json([
            'message' => 'Preference deleted successfully',
        ]);
    }

    public function bulkUpdate(BulkUpdateUserPreferenceRequest $request): JsonResponse
    {
        try {
            Log::info('Starting bulk update', ['request' => $request->all()]);
            
            DB::beginTransaction();

            // Delete existing preferences if specified
            if ($request->clear_existing) {
                Log::info('Deleting existing preferences');
                auth()->user()->preferences()->delete();
            }

            // Create new preferences
            $preferences = collect();
            foreach ($request->preferences as $pref) {
                Log::info('Creating preference', ['preference' => $pref]);
                $preference = auth()->user()->preferences()->create([
                    'category_id' => $pref['category_id'] ?? null,
                    'source_id' => $pref['source_id'] ?? null,
                    'preference_type' => $pref['preference_type'],
                    'priority' => $pref['priority'] ?? 0,
                ]);
                $preferences->push($preference->load(['category', 'source']));
            }

            DB::commit();
            Log::info('Bulk update completed successfully');

            return response()->json([
                'message' => 'Preferences updated successfully',
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update preferences', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to update preferences',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
} 