<?php

namespace App\Modules\EmergencyResponse\Services;

use App\Models\EmergencyAlert;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmergencyResponseService
{
    /**
     * Get paginated emergency alerts
     */
    public function getPaginated(array $filters = [], int $perPage = 25): Collection
    {
        $query = EmergencyAlert::with(['user', 'branch', 'responder'])
            ->latest('triggered_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('location_description', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
                  ->orWhereHas('user', function ($userQuery) use ($filters) {
                      $userQuery->where('name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        return $query->get();
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(string $branchId): array
    {
        $query = EmergencyAlert::where('branch_id', $branchId);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'triggered')->count(),
            'responded' => (clone $query)->where('status', 'responded')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'today' => (clone $query)->whereDate('triggered_at', today())->count(),
            'this_week' => (clone $query)->whereBetween('triggered_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $query)->whereMonth('triggered_at', now()->month)->count(),
            'avg_response_time' => $this->calculateAverageResponseTime($branchId),
        ];
    }

    /**
     * Create a new emergency alert
     */
    public function create(array $data): EmergencyAlert
    {
        $data['user_id'] = auth()->id();
        $data['branch_id'] = auth()->user()->branch_id;
        $data['triggered_at'] = now();
        $data['status'] = 'triggered';

        return EmergencyAlert::create($data);
    }

    /**
     * Update an emergency alert
     */
    public function update(EmergencyAlert $alert, array $data): EmergencyAlert
    {
        $alert->update($data);

        return $alert->fresh();
    }

    /**
     * Respond to an emergency alert
     */
    public function respond(EmergencyAlert $alert): EmergencyAlert
    {
        $alert->update([
            'status' => 'responded',
            'responded_at' => now(),
            'responder_id' => auth()->id(),
        ]);

        return $alert->fresh();
    }

    /**
     * Resolve an emergency alert
     */
    public function resolve(EmergencyAlert $alert, ?string $notes = null): EmergencyAlert
    {
        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'responder_id' => $alert->responder_id ?? auth()->id(),
            'response_notes' => $notes,
        ]);

        return $alert->fresh();
    }

    /**
     * Cancel an emergency alert
     */
    public function cancel(EmergencyAlert $alert): EmergencyAlert
    {
        $alert->update([
            'status' => 'cancelled',
        ]);

        return $alert->fresh();
    }

    /**
     * Get active alerts for a branch
     */
    public function getActiveAlerts(string $branchId): Collection
    {
        return EmergencyAlert::where('branch_id', $branchId)
            ->whereIn('status', ['triggered', 'responded'])
            ->with(['user', 'responder'])
            ->latest('triggered_at')
            ->get();
    }

    /**
     * Calculate average response time in minutes
     */
    private function calculateAverageResponseTime(string $branchId): ?int
    {
        $alerts = EmergencyAlert::where('branch_id', $branchId)
            ->whereNotNull('responded_at')
            ->get(['triggered_at', 'responded_at']);

        if ($alerts->isEmpty()) {
            return null;
        }

        $totalMinutes = $alerts->sum(function ($alert) {
            return $alert->triggered_at->diffInMinutes($alert->responded_at);
        });

        return round($totalMinutes / $alerts->count());
    }
}
