<?php

namespace App\Modules\TeamManagement\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Get authenticated user details.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'position' => $user->position,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                ] : null,
                'role' => $user->getRoleNames()->first(),
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Issue authentication token for mobile device.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact your manager.'],
            ]);
        }

        // Create token with abilities based on user role
        $abilities = $this->getAbilitiesForUser($user);
        $token = $user->createToken($request->device_name, $abilities, now()->addDays(30));

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'position' => $user->position,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                ] : null,
                'role' => $user->getRoleNames()->first(),
            ],
        ], 200);
    }

    /**
     * Revoke current authentication token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revoked successfully. You have been logged out.',
        ]);
    }

    /**
     * Revoke specific token by ID.
     */
    public function revokeToken(Request $request, string $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return response()->json([
                'message' => 'Token not found.',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }

    /**
     * Get token abilities based on user role.
     *
     * @return array<int, string>
     */
    protected function getAbilitiesForUser(User $user): array
    {
        $role = $user->getRoleNames()->first();

        return match ($role) {
            'Admin' => ['*'], // All abilities
            'Manager' => [
                'team-members:view',
                'vehicles:view',
                'vehicles:assign',
                'inspections:view',
                'inspections:create',
                'journeys:view',
            ],
            'Supervisor' => [
                'team-members:view-branch',
                'vehicles:view-branch',
                'inspections:view-branch',
                'inspections:create',
                'journeys:view-branch',
            ],
            'Employee' => [
                'vehicles:view-assigned',
                'inspections:view-own',
                'inspections:create',
                'journeys:view-own',
                'journeys:create',
            ],
            default => [],
        };
    }
}
