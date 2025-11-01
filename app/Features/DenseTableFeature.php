<?php

namespace App\Features;

use App\Models\User;
use Illuminate\Support\Lottery;

class DenseTableFeature
{
    /**
     * Resolve the initial value of the feature flag.
     *
     * 3-Phase Rollout Strategy:
     * - Phase 1 (Week 1): Sydney Operations Centre only (primary testing ground)
     * - Phase 2 (Week 2-3): 50% A/B test across all branches
     * - Phase 3 (Week 4+): 100% rollout to all users
     *
     * Emergency disable: Set DENSE_TABLE_ENABLED=false in .env
     *
     * @param User $user
     * @return bool
     */
    public function resolve(User $user): bool
    {
        // Emergency disable switch
        if (!config('app.dense_table_enabled', true)) {
            return false;
        }

        // Phase 1: Sydney Operations Centre only (Week 1: Nov 1-7, 2025)
        // Primary testing ground before wider rollout
        if ($user->branch_id === '019a2a03-5c3e-72ce-90f4-423356c32441') {
            return true;
        }

        // Phase 2: 50% A/B test (Week 2-3: Nov 8-21, 2025)
        // Gradual rollout with performance monitoring
        if (now()->isAfter('2025-11-08')) {
            return Lottery::odds(1, 2)->choose();
        }

        // Phase 3: 100% rollout (Week 4+: Nov 22+, 2025)
        // Full production deployment
        if (now()->isAfter('2025-11-22')) {
            return true;
        }

        // Default: feature disabled
        return false;
    }
}
