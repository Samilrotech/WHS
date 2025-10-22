<?php

namespace App\Modules\ContractorManagement\Services;

use App\Modules\ContractorManagement\Models\Visitor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class VisitorService
{
    /**
     * Get all visitors for current branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return Visitor::where('branch_id', $branchId)
            ->with(['host', 'briefer'])
            ->latest()
            ->get();
    }

    /**
     * Get visitors currently on site
     */
    public function getOnSite(): Collection
    {
        return Visitor::onSite()
            ->with(['host', 'briefer'])
            ->get();
    }

    /**
     * Get expected visitors
     */
    public function getExpected(): Collection
    {
        return Visitor::expected()
            ->with(['host'])
            ->get();
    }

    /**
     * Get visitors expected today
     */
    public function getExpectedToday(): Collection
    {
        return Visitor::expectedToday()
            ->with(['host'])
            ->get();
    }

    /**
     * Get overdue visitors
     */
    public function getOverdue(): Collection
    {
        return Visitor::overdue()
            ->with(['host'])
            ->get();
    }

    /**
     * Create new visitor
     */
    public function create(array $data): Visitor
    {
        try {
            DB::beginTransaction();

            // Ensure branch_id is set from authenticated user if not provided
            if (!isset($data['branch_id']) && auth()->check()) {
                $data['branch_id'] = auth()->user()->branch_id;
            }

            $visitor = Visitor::create($data);

            Log::info('Visitor created', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'company' => $visitor->company,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $visitor;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create visitor', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update visitor
     */
    public function update(Visitor $visitor, array $data): Visitor
    {
        try {
            DB::beginTransaction();

            $visitor->update($data);

            Log::info('Visitor updated', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $visitor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update visitor', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Complete safety briefing for visitor
     */
    public function completeSafetyBriefing(Visitor $visitor): Visitor
    {
        try {
            DB::beginTransaction();

            $visitor->update([
                'safety_briefing_completed' => true,
                'briefed_by' => auth()->id(),
                'briefing_completed_at' => now(),
            ]);

            Log::info('Visitor safety briefing completed', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'briefed_by' => auth()->id(),
            ]);

            DB::commit();

            return $visitor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete safety briefing', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Record visitor arrival
     */
    public function recordArrival(Visitor $visitor, ?string $badgeNumber = null): Visitor
    {
        try {
            DB::beginTransaction();

            // Check if visitor has completed safety briefing
            if (!$visitor->hasSafetyBriefing()) {
                throw new \Exception('Visitor must complete safety briefing before arrival');
            }

            $visitor->update([
                'actual_arrival' => now(),
                'status' => 'on_site',
                'badge_number' => $badgeNumber,
            ]);

            Log::info('Visitor arrival recorded', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'badge_number' => $badgeNumber,
            ]);

            DB::commit();

            return $visitor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record visitor arrival', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Record visitor departure
     */
    public function recordDeparture(Visitor $visitor): Visitor
    {
        try {
            DB::beginTransaction();

            // Check if visitor is on site
            if (!$visitor->isOnSite()) {
                throw new \Exception('Visitor is not currently on site');
            }

            $visitor->update([
                'actual_departure' => now(),
                'status' => 'departed',
            ]);

            // Sign out visitor if they have an active sign-in log
            if ($visitor->isSignedIn()) {
                $signInLog = $visitor->signInLogs()
                    ->where('status', 'signed_in')
                    ->whereNull('signed_out_at')
                    ->latest()
                    ->first();

                if ($signInLog) {
                    $signInLog->update([
                        'signed_out_at' => now(),
                        'status' => 'signed_out',
                    ]);
                }
            }

            Log::info('Visitor departure recorded', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'time_on_site' => $visitor->time_on_site,
            ]);

            DB::commit();

            return $visitor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record visitor departure', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel visitor visit
     */
    public function cancel(Visitor $visitor, string $reason): Visitor
    {
        try {
            DB::beginTransaction();

            $visitor->update([
                'status' => 'cancelled',
                'notes' => ($visitor->notes ?? '') . "\n\nCancelled on " . now()->format('Y-m-d H:i:s') . ": {$reason}",
            ]);

            Log::info('Visitor visit cancelled', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $visitor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel visitor visit', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete visitor (soft delete)
     */
    public function delete(Visitor $visitor): bool
    {
        try {
            DB::beginTransaction();

            // Check if visitor is currently on site
            if ($visitor->isOnSite()) {
                throw new \Exception('Cannot delete visitor who is currently on site');
            }

            $visitor->delete();

            Log::warning('Visitor deleted', [
                'visitor_id' => $visitor->id,
                'name' => $visitor->full_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete visitor', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check and notify about overdue visitors
     */
    public function checkOverdueVisitors(): array
    {
        $overdueVisitors = $this->getOverdue();

        $notifications = [];

        foreach ($overdueVisitors as $visitor) {
            $minutesOverdue = $visitor->expected_departure
                ? $visitor->expected_departure->diffInMinutes(now())
                : null;

            $notifications[] = [
                'visitor' => $visitor,
                'expected_departure' => $visitor->expected_departure,
                'minutes_overdue' => $minutesOverdue,
                'host' => $visitor->host,
                'urgency' => $minutesOverdue > 60 ? 'high' : 'medium',
            ];
        }

        return $notifications;
    }

    /**
     * Get visitor statistics
     */
    public function getStatistics(?string $branchId = null): array
    {
        $query = Visitor::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $total = $query->count();
        $onSite = (clone $query)->onSite()->count();
        $expected = (clone $query)->expected()->count();
        $overdue = (clone $query)->overdue()->count();

        return [
            'total_visitors' => $total,
            'currently_on_site' => $onSite,
            'expected_today' => $expected,
            'overdue' => $overdue,
        ];
    }
}
