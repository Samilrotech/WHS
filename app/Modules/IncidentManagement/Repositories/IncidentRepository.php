<?php

namespace App\Modules\IncidentManagement\Repositories;

use App\Modules\IncidentManagement\Models\Incident;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentRepository
{
    /**
     * Get paginated incidents with relationships
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Incident::query()
            ->with(['user', 'branch', 'assignedTo', 'witnesses', 'photos']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->type($filters['type']);
        }

        if (isset($filters['severity'])) {
            $query->severity($filters['severity']);
        }

        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                    ->orWhere('location_specific', 'like', "%{$filters['search']}%")
                    ->orWhere('location_branch', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['date_from'])) {
            $query->where('incident_datetime', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('incident_datetime', '<=', $filters['date_to']);
        }

        return $query->latest('incident_datetime')->paginate($perPage);
    }

    /**
     * Find incident by ID with relationships
     */
    public function findOrFail(string $id): Incident
    {
        return Incident::with(['user', 'branch', 'assignedTo', 'witnesses', 'photos'])
            ->findOrFail($id);
    }

    /**
     * Create a new incident
     */
    public function create(array $data): Incident
    {
        return Incident::create($data);
    }

    /**
     * Update an existing incident
     */
    public function update(Incident $incident, array $data): Incident
    {
        $incident->update($data);
        return $incident->fresh();
    }

    /**
     * Delete an incident
     */
    public function delete(Incident $incident): bool
    {
        return $incident->delete();
    }

    /**
     * Get recent incidents (last 30 days)
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Incident::recent()
            ->with(['user', 'branch'])
            ->limit($limit)
            ->latest('incident_datetime')
            ->get();
    }

    /**
     * Get critical/high severity incidents
     */
    public function getCritical(): Collection
    {
        return Incident::whereIn('severity', ['critical', 'high'])
            ->where('status', '!=', 'closed')
            ->with(['user', 'branch', 'assignedTo'])
            ->latest('incident_datetime')
            ->get();
    }

    /**
     * Get incidents by branch
     */
    public function getByBranch(string $branchId, int $limit = null): Collection
    {
        $query = Incident::where('branch_id', $branchId)
            ->with(['user', 'witnesses', 'photos'])
            ->latest('incident_datetime');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get incident statistics
     */
    public function getStatistics(?string $branchId = null): array
    {
        $query = Incident::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return [
            'total' => $query->count(),
            'by_severity' => [
                'low' => (clone $query)->severity('low')->count(),
                'medium' => (clone $query)->severity('medium')->count(),
                'high' => (clone $query)->severity('high')->count(),
                'critical' => (clone $query)->severity('critical')->count(),
            ],
            'by_status' => [
                'reported' => (clone $query)->status('reported')->count(),
                'investigating' => (clone $query)->status('investigating')->count(),
                'resolved' => (clone $query)->status('resolved')->count(),
                'closed' => (clone $query)->status('closed')->count(),
            ],
            'by_type' => [
                'injury' => (clone $query)->type('injury')->count(),
                'near_miss' => (clone $query)->type('near-miss')->count(),
                'property_damage' => (clone $query)->type('property-damage')->count(),
                'environmental' => (clone $query)->type('environmental')->count(),
                'security' => (clone $query)->type('security')->count(),
            ],
        ];
    }
}
