<?php

namespace App\Modules\IncidentManagement\Services;

use App\Modules\IncidentManagement\Models\Incident;
use App\Modules\IncidentManagement\Models\IncidentPhoto;
use App\Modules\IncidentManagement\Models\Witness;
use App\Modules\IncidentManagement\Repositories\IncidentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IncidentService
{
    public function __construct(
        private IncidentRepository $repository
    ) {}

    /**
     * Create a new incident with photos and witnesses
     */
    public function createIncident(array $data): Incident
    {
        return DB::transaction(function () use ($data) {
            // Extract nested data
            $photos = $data['photos'] ?? [];
            $witnesses = $data['witnesses'] ?? [];
            $voiceNote = $data['voice_note'] ?? null;
            unset($data['photos'], $data['witnesses'], $data['voice_note']);

            // Set user and branch from auth
            $data['user_id'] = auth()->id();
            $data['branch_id'] = auth()->user()->branch_id;

            // Handle voice note upload
            if ($voiceNote instanceof UploadedFile) {
                $data['voice_note_path'] = $this->storeVoiceNote($voiceNote);
            }

            // Auto-assign severity if critical conditions met
            if (!isset($data['severity'])) {
                $data['severity'] = $this->determineSeverity($data);
            }

            // Create incident
            $incident = $this->repository->create($data);

            // Handle photo uploads
            if (!empty($photos)) {
                $this->attachPhotos($incident, $photos);
            }

            // Create witness records
            if (!empty($witnesses)) {
                $this->createWitnesses($incident, $witnesses);
            }

            // Broadcast real-time notification
            // broadcast(new IncidentCreated($incident))->toOthers();

            return $incident->fresh(['user', 'branch', 'photos', 'witnesses']);
        });
    }

    /**
     * Update an existing incident
     */
    public function updateIncident(Incident $incident, array $data): Incident
    {
        return DB::transaction(function () use ($incident, $data) {
            // Extract nested data
            $photos = $data['photos'] ?? [];
            $witnesses = $data['witnesses'] ?? [];
            unset($data['photos'], $data['witnesses']);

            // Update incident
            $incident = $this->repository->update($incident, $data);

            // Handle new photos
            if (!empty($photos)) {
                $this->attachPhotos($incident, $photos);
            }

            // Handle witnesses
            if (!empty($witnesses)) {
                $this->syncWitnesses($incident, $witnesses);
            }

            return $incident->fresh(['user', 'branch', 'photos', 'witnesses']);
        });
    }

    /**
     * Assign incident to a user for investigation
     */
    public function assignIncident(Incident $incident, string $userId): Incident
    {
        return $this->repository->update($incident, [
            'assigned_to' => $userId,
            'status' => 'investigating',
        ]);
    }

    /**
     * Close an incident with root cause analysis
     */
    public function closeIncident(Incident $incident, string $rootCause): Incident
    {
        return $this->repository->update($incident, [
            'status' => 'closed',
            'root_cause' => $rootCause,
        ]);
    }

    /**
     * Store uploaded photos for an incident
     */
    private function attachPhotos(Incident $incident, array $photos): void
    {
        foreach ($photos as $index => $photo) {
            if ($photo instanceof UploadedFile) {
                $path = $photo->store('incidents/' . $incident->id, 'public');

                IncidentPhoto::create([
                    'incident_id' => $incident->id,
                    'file_path' => $path,
                    'file_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize(),
                    'display_order' => $index,
                ]);
            }
        }
    }

    /**
     * Store voice note file
     */
    private function storeVoiceNote(UploadedFile $file): string
    {
        return $file->store('incidents/voice-notes', 'public');
    }

    /**
     * Create witness records
     */
    private function createWitnesses(Incident $incident, array $witnesses): void
    {
        foreach ($witnesses as $witnessData) {
            Witness::create([
                'incident_id' => $incident->id,
                'name' => $witnessData['name'],
                'contact_number' => $witnessData['contact_number'] ?? null,
                'email' => $witnessData['email'] ?? null,
                'statement' => $witnessData['statement'],
                'statement_taken_at' => now(),
                'taken_by_user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Sync witnesses (update existing, create new)
     */
    private function syncWitnesses(Incident $incident, array $witnesses): void
    {
        foreach ($witnesses as $witnessData) {
            if (isset($witnessData['id'])) {
                // Update existing
                Witness::find($witnessData['id'])?->update($witnessData);
            } else {
                // Create new
                $this->createWitnesses($incident, [$witnessData]);
            }
        }
    }

    /**
     * Auto-determine severity based on incident data
     */
    private function determineSeverity(array $data): string
    {
        // Critical if emergency or authorities required
        if (($data['requires_emergency'] ?? false) || ($data['notify_authorities'] ?? false)) {
            return 'critical';
        }

        // High if injury type
        if (($data['type'] ?? '') === 'injury') {
            return 'high';
        }

        // Medium if property damage or environmental
        if (in_array($data['type'] ?? '', ['property-damage', 'environmental'])) {
            return 'medium';
        }

        // Default to low
        return 'low';
    }

    /**
     * Delete incident photo
     */
    public function deletePhoto(string $photoId): bool
    {
        $photo = IncidentPhoto::findOrFail($photoId);
        return $photo->delete();
    }

    /**
     * Get incident statistics
     */
    public function getStatistics(?string $branchId = null): array
    {
        return $this->repository->getStatistics($branchId);
    }
}
