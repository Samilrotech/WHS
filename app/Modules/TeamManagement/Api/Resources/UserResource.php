<?php

namespace App\Modules\TeamManagement\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'employment_status' => $this->employment_status,
            'employment_start_date' => $this->employment_start_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'branch' => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null,
            'role' => $this->getRoleNames()->first(),
            'current_vehicle' => $this->when(
                $this->relationLoaded('currentVehicleAssignment'),
                function () {
                    if ($this->currentVehicleAssignment && $this->currentVehicleAssignment->vehicle) {
                        return [
                            'id' => $this->currentVehicleAssignment->vehicle->id,
                            'registration' => $this->currentVehicleAssignment->vehicle->registration,
                            'make' => $this->currentVehicleAssignment->vehicle->make,
                            'model' => $this->currentVehicleAssignment->vehicle->model,
                            'assigned_at' => $this->currentVehicleAssignment->assigned_at?->toIso8601String(),
                        ];
                    }
                    return null;
                }
            ),
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
