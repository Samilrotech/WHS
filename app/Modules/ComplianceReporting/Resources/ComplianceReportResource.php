<?php

namespace App\Modules\ComplianceReporting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceReportResource extends JsonResource
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
            'branch_id' => $this->branch_id,
            'report_number' => $this->report_number,

            // Details
            'title' => $this->title,
            'report_type' => $this->report_type,
            'report_type_label' => $this->getReportTypeLabel(),
            'period' => $this->period,

            // Date range
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'report_date' => $this->report_date->toDateString(),

            // Content
            'requirements_included' => $this->requirements_included,
            'metrics' => $this->metrics,
            'executive_summary' => $this->executive_summary,
            'key_findings' => $this->key_findings,
            'recommendations' => $this->recommendations,

            // Status
            'status' => $this->status,
            'status_badge_color' => $this->getStatusBadgeColor(),
            'is_draft' => $this->isDraft(),
            'is_approved' => $this->isApproved(),
            'is_published' => $this->isPublished(),

            // Approvals
            'approved_at' => $this->approved_at?->toIso8601String(),

            // File attachments
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,

            // Relationships
            'creator' => $this->when($this->creator, [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ]),
            'reviewer' => $this->when($this->reviewer, [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
                'email' => $this->reviewer?->email,
            ]),
            'approver' => $this->when($this->approver, [
                'id' => $this->approver?->id,
                'name' => $this->approver?->name,
                'email' => $this->approver?->email,
            ]),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
