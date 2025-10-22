<?php

namespace App\Modules\JourneyManagement\Services;

use App\Modules\JourneyManagement\Models\Journey;
use App\Modules\JourneyManagement\Models\JourneyCheckpoint;
use Illuminate\Support\Collection;

class JourneyService
{
    /**
     * Get all journeys with pagination and filtering
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        $query = Journey::with(['user', 'vehicle', 'latestCheckpoint'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new journey
     */
    public function create(array $data): Journey
    {
        $data['branch_id'] = auth()->user()->branch_id;

        // Calculate next check-in due time
        if (isset($data['planned_start_time']) && isset($data['checkin_interval_minutes'])) {
            $data['next_checkin_due'] = now()->parse($data['planned_start_time'])
                ->addMinutes($data['checkin_interval_minutes']);
        }

        return Journey::create($data);
    }

    /**
     * Start a journey
     */
    public function startJourney(Journey $journey): Journey
    {
        $journey->update([
            'status' => 'active',
            'actual_start_time' => now(),
            'next_checkin_due' => now()->addMinutes($journey->checkin_interval_minutes),
        ]);

        // Create initial checkpoint
        $this->recordCheckpoint($journey, [
            'type' => 'automatic',
            'status' => 'ok',
            'notes' => 'Journey started',
        ]);

        return $journey->fresh();
    }

    /**
     * Complete a journey
     */
    public function completeJourney(Journey $journey, ?string $completionNotes = null): Journey
    {
        $journey->update([
            'status' => 'completed',
            'actual_end_time' => now(),
            'completion_notes' => $completionNotes,
        ]);

        // Create completion checkpoint
        $this->recordCheckpoint($journey, [
            'type' => 'manual',
            'status' => 'ok',
            'notes' => 'Journey completed',
        ]);

        return $journey->fresh();
    }

    /**
     * Record a check-in checkpoint
     */
    public function recordCheckpoint(Journey $journey, array $data): JourneyCheckpoint
    {
        $data['journey_id'] = $journey->id;
        $data['checkin_time'] = $data['checkin_time'] ?? now();

        $checkpoint = JourneyCheckpoint::create($data);

        // Update journey's last check-in time and next due time
        $journey->update([
            'last_checkin_time' => $checkpoint->checkin_time,
            'next_checkin_due' => $checkpoint->checkin_time->addMinutes($journey->checkin_interval_minutes),
            'checkin_overdue' => false,
        ]);

        // If emergency checkpoint, update journey status
        if ($checkpoint->isEmergency()) {
            $journey->update(['status' => 'emergency']);
        }

        return $checkpoint;
    }

    /**
     * Mark journey as emergency
     */
    public function triggerEmergency(Journey $journey, ?string $notes = null): Journey
    {
        $journey->update(['status' => 'emergency']);

        $this->recordCheckpoint($journey, [
            'type' => 'emergency',
            'status' => 'emergency',
            'notes' => $notes ?? 'Emergency assistance requested',
        ]);

        return $journey->fresh();
    }

    /**
     * Get overdue journeys (missed check-ins)
     */
    public function getOverdueJourneys(): Collection
    {
        return Journey::active()
            ->where('next_checkin_due', '<', now())
            ->with(['user', 'vehicle'])
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_journeys' => Journey::count(),
            'active_journeys' => Journey::where('status', 'active')->count(),
            'overdue_journeys' => Journey::overdue()->count(),
            'emergency_journeys' => Journey::where('status', 'emergency')->count(),
            'completed_today' => Journey::where('status', 'completed')
                ->whereDate('actual_end_time', today())
                ->count(),
        ];
    }
}
