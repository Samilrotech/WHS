<?php

namespace App\Modules\RiskAssessment\Services;

use App\Modules\RiskAssessment\Models\RiskAssessment;
use App\Modules\RiskAssessment\Repositories\RiskAssessmentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RiskAssessmentService
{
    public function __construct(
        private RiskAssessmentRepository $repository,
        private RiskMatrixService $matrixService
    ) {}

    /**
     * Get paginated risk assessments
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters);
    }

    /**
     * Create new risk assessment with hazards and controls
     *
     * @param array $data
     * @return RiskAssessment
     * @throws \Exception
     */
    public function create(array $data): RiskAssessment
    {
        DB::beginTransaction();
        try {
            // Auto-set branch_id from authenticated user
            $data['branch_id'] = auth()->user()->branch_id;
            $data['user_id'] = auth()->id();

            // Create risk assessment (risk scores auto-calculated in model)
            $assessment = $this->repository->create($data);

            // Create hazards and control measures if provided
            if (!empty($data['hazards'])) {
                foreach ($data['hazards'] as $hazardData) {
                    $hazard = $assessment->hazards()->create([
                        'branch_id' => $assessment->branch_id,
                        'hazard_type' => $hazardData['type'] ?? '',
                        'description' => $hazardData['description'] ?? '',
                        'potential_consequences' => $hazardData['consequences'] ?? '',
                        'persons_at_risk' => $hazardData['persons_at_risk'] ?? 0,
                        'affected_groups' => $hazardData['affected_groups'] ?? [],
                    ]);

                    // Add control measures to the hazard
                    if (!empty($hazardData['controls'])) {
                        foreach ($hazardData['controls'] as $controlData) {
                            $hazard->controlMeasures()->create([
                                'hierarchy' => $controlData['hierarchy'] ?? 'ppe',
                                'description' => $controlData['description'] ?? '',
                                'responsible_person' => $controlData['responsible_person'] ?? null,
                                'implementation_date' => $controlData['implementation_date'] ?? null,
                                'status' => $controlData['status'] ?? 'planned',
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return $assessment->load(['hazards.controlMeasures', 'user', 'branch']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update risk assessment
     *
     * @param RiskAssessment $assessment
     * @param array $data
     * @return RiskAssessment
     * @throws \Exception
     */
    public function update(RiskAssessment $assessment, array $data): RiskAssessment
    {
        DB::beginTransaction();
        try {
            // Update main assessment (risk scores auto-calculated if likelihood/consequence changed)
            $assessment = $this->repository->update($assessment, $data);

            // Update hazards if provided
            if (isset($data['hazards'])) {
                // Delete existing hazards (cascade will delete controls)
                $assessment->hazards()->delete();

                // Create new hazards
                foreach ($data['hazards'] as $hazardData) {
                    $hazard = $assessment->hazards()->create([
                        'branch_id' => $assessment->branch_id,
                        'hazard_type' => $hazardData['type'] ?? '',
                        'description' => $hazardData['description'] ?? '',
                        'potential_consequences' => $hazardData['consequences'] ?? '',
                        'persons_at_risk' => $hazardData['persons_at_risk'] ?? 0,
                        'affected_groups' => $hazardData['affected_groups'] ?? [],
                    ]);

                    // Add control measures
                    if (!empty($hazardData['controls'])) {
                        foreach ($hazardData['controls'] as $controlData) {
                            $hazard->controlMeasures()->create([
                                'hierarchy' => $controlData['hierarchy'] ?? 'ppe',
                                'description' => $controlData['description'] ?? '',
                                'responsible_person' => $controlData['responsible_person'] ?? null,
                                'implementation_date' => $controlData['implementation_date'] ?? null,
                                'status' => $controlData['status'] ?? 'planned',
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return $assessment->load(['hazards.controlMeasures', 'user', 'branch']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve risk assessment
     *
     * @param RiskAssessment $assessment
     * @return RiskAssessment
     */
    public function approve(RiskAssessment $assessment): RiskAssessment
    {
        $assessment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $assessment;
    }

    /**
     * Reject risk assessment
     *
     * @param RiskAssessment $assessment
     * @return RiskAssessment
     */
    public function reject(RiskAssessment $assessment): RiskAssessment
    {
        $assessment->update([
            'status' => 'rejected',
        ]);

        return $assessment;
    }

    /**
     * Submit risk assessment for approval
     *
     * @param RiskAssessment $assessment
     * @return RiskAssessment
     */
    public function submit(RiskAssessment $assessment): RiskAssessment
    {
        $assessment->update([
            'status' => 'submitted',
        ]);

        return $assessment;
    }

    /**
     * Get high risk assessments
     *
     * @param string $branchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighRisk(string $branchId)
    {
        return $this->repository->getHighRisk($branchId);
    }

    /**
     * Get statistics for dashboard
     *
     * @param string $branchId
     * @return array
     */
    public function getStatistics(string $branchId): array
    {
        return $this->matrixService->getStatistics($branchId);
    }
}
