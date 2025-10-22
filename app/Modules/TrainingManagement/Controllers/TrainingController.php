<?php

namespace App\Modules\TrainingManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\TrainingManagement\Models\Certification;
use App\Modules\TrainingManagement\Models\TrainingCourse;
use App\Modules\TrainingManagement\Models\TrainingRecord;
use App\Modules\TrainingManagement\Services\TrainingService;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function __construct(
        protected TrainingService $trainingService
    ) {}

    /**
     * Display main training management dashboard
     */
    public function index()
    {
        return view('content.TrainingManagement.Index');
    }

    // ============================================================================
    // TRAINING COURSES
    // ============================================================================

    /**
     * Display training courses index
     */
    public function coursesIndex(Request $request)
    {
        $query = TrainingCourse::with(['creator', 'branch'])
            ->latest();

        // Filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('mandatory')) {
            $query->mandatory();
        }

        if ($request->filled('delivery_method')) {
            $query->byDeliveryMethod($request->delivery_method);
        }

        $courses = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_courses' => TrainingCourse::active()->count(),
            'mandatory_courses' => TrainingCourse::active()->mandatory()->count(),
            'cpd_accredited' => TrainingCourse::active()->cpdAccredited()->count(),
            'total_enrolled' => TrainingRecord::whereIn('status', ['assigned', 'in_progress'])->count(),
        ];

        return view('content.Training/Courses.Index', [
            'courses' => $courses,
            'statistics' => $stats,
            'filters' => $request->only(['category', 'status', 'mandatory', 'delivery_method']),
        ]);
    }

    /**
     * Store new training course
     */
    public function courseStore(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|unique:training_courses,course_code',
            'course_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:safety_induction,driver_training,vehicle_operation,load_securement,fatigue_management,emergency_response,hazmat_handling,forklift_operation,manual_handling,first_aid,whs_compliance,other',
            'duration_hours' => 'nullable|integer|min:1',
            'validity_months' => 'nullable|integer|min:1',
            'is_cpd_accredited' => 'boolean',
            'cpd_points' => 'nullable|string',
            'requires_assessment' => 'boolean',
            'pass_score' => 'nullable|numeric|min:0|max:100',
            'delivery_method' => 'required|in:online,classroom,hands_on,blended',
            'is_mandatory' => 'boolean',
            'frequency' => 'required|in:once,annual,biennial,triennial,custom',
            'cost_per_person' => 'nullable|numeric|min:0',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        $validated['created_by_user_id'] = auth()->id();

        $course = TrainingCourse::create($validated);

        return redirect()->route('training.courses.index')
            ->with('success', 'Training course created successfully.');
    }

    /**
     * Update training course
     */
    public function courseUpdate(Request $request, TrainingCourse $course)
    {
        $validated = $request->validate([
            'course_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,archived,draft',
            'duration_hours' => 'nullable|integer|min:1',
            'cost_per_person' => 'nullable|numeric|min:0',
        ]);

        $course->update($validated);

        return back()->with('success', 'Course updated successfully.');
    }

    // ============================================================================
    // TRAINING RECORDS
    // ============================================================================

    /**
     * Display training records index
     */
    public function recordsIndex(Request $request)
    {
        $query = TrainingRecord::with(['user', 'trainingCourse', 'assignedBy'])
            ->latest();

        // Filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('course_id')) {
            $query->byCourse($request->course_id);
        }

        if ($request->filled('overdue')) {
            $query->overdue();
        }

        $records = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'assigned' => TrainingRecord::where('status', 'assigned')->count(),
            'in_progress' => TrainingRecord::where('status', 'in_progress')->count(),
            'completed' => TrainingRecord::completed()->count(),
            'overdue' => TrainingRecord::overdue()->count(),
        ];

        return view('content.Training/Records.Index', [
            'records' => $records,
            'statistics' => $stats,
            'filters' => $request->only(['status', 'user_id', 'course_id', 'overdue']),
        ]);
    }

    /**
     * Assign training to user
     */
    public function assignTraining(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'training_course_id' => 'required|exists:training_courses,id',
            'due_date' => 'nullable|date|after:today',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $course = TrainingCourse::findOrFail($validated['training_course_id']);

        $record = $this->trainingService->assignTraining(
            $user,
            $course,
            auth()->user(),
            $validated['due_date'] ?? null
        );

        return back()->with('success', 'Training assigned successfully.');
    }

    /**
     * Bulk assign training
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'training_course_id' => 'required|exists:training_courses,id',
            'due_date' => 'nullable|date|after:today',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();
        $course = TrainingCourse::findOrFail($validated['training_course_id']);

        $this->trainingService->bulkAssignTraining(
            $users,
            $course,
            auth()->user(),
            $validated['due_date'] ?? null
        );

        return back()->with('success', count($users) . ' users assigned to training.');
    }

    /**
     * Commence training
     */
    public function commenceTraining(TrainingRecord $record)
    {
        $this->trainingService->commenceTraining($record);

        return back()->with('success', 'Training commenced successfully.');
    }

    /**
     * Update training progress
     */
    public function updateProgress(Request $request, TrainingRecord $record)
    {
        $validated = $request->validate([
            'completion_percentage' => 'required|integer|min:0|max:100',
            'progress_notes' => 'nullable|string',
        ]);

        $this->trainingService->updateProgress(
            $record,
            $validated['completion_percentage'],
            $validated['progress_notes'] ?? null
        );

        return back()->with('success', 'Progress updated successfully.');
    }

    /**
     * Complete training
     */
    public function completeTraining(Request $request, TrainingRecord $record)
    {
        $validated = $request->validate([
            'assessment_score' => 'nullable|numeric|min:0|max:100',
            'assessment_feedback' => 'nullable|string',
            'certificate_number' => 'nullable|string',
        ]);

        $this->trainingService->completeTraining(
            $record,
            auth()->user(),
            $validated['assessment_score'] ?? null,
            $validated['assessment_feedback'] ?? null,
            $validated['certificate_number'] ?? null
        );

        return back()->with('success', 'Training completed successfully.');
    }

    /**
     * Record assessment attempt
     */
    public function recordAssessment(Request $request, TrainingRecord $record)
    {
        $validated = $request->validate([
            'assessment_score' => 'required|numeric|min:0|max:100',
            'assessment_passed' => 'required|boolean',
            'assessment_feedback' => 'nullable|string',
        ]);

        $this->trainingService->recordAssessmentAttempt(
            $record,
            $validated['assessment_score'],
            $validated['assessment_passed'],
            $validated['assessment_feedback'] ?? null
        );

        return back()->with('success', 'Assessment recorded successfully.');
    }

    // ============================================================================
    // CERTIFICATIONS
    // ============================================================================

    /**
     * Display certifications index
     */
    public function certificationsIndex(Request $request)
    {
        $query = Certification::with(['user', 'verifiedBy'])
            ->latest();

        // Filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('expiring')) {
            $query->expiringSoon(30);
        }

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        $certifications = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'active' => Certification::active()->count(),
            'expiring_soon' => Certification::expiringSoon(30)->count(),
            'expired' => Certification::expired()->count(),
            'pending_verification' => Certification::pending()->count(),
        ];

        return view('content.Training/Certifications.Index', [
            'certifications' => $certifications,
            'statistics' => $stats,
            'filters' => $request->only(['type', 'status', 'expiring', 'user_id']),
        ]);
    }

    /**
     * Store certification
     */
    public function certificationStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'certification_type' => 'required|in:driver_license,forklift_license,heavy_vehicle_license,dangerous_goods_license,work_at_heights,confined_space,first_aid,cpd_certification,load_securement,fatigue_management,other',
            'certification_name' => 'required|string',
            'license_number' => 'nullable|string|unique:certifications,license_number',
            'issuing_authority' => 'required|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'license_classes' => 'nullable|array',
            'verification_status' => 'required|in:pending,verified,expired,suspended,revoked',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        $certification = Certification::create($validated);

        return back()->with('success', 'Certification created successfully.');
    }

    /**
     * Verify certification
     */
    public function verifyCertification(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'verification_notes' => 'nullable|string',
        ]);

        $certification->verify(auth()->user(), $validated['verification_notes'] ?? null);

        return back()->with('success', 'Certification verified successfully.');
    }

    /**
     * Renew certification
     */
    public function renewCertification(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'validity_months' => 'required|integer|min:1|max:120',
        ]);

        $certification->renew($validated['validity_months']);

        return back()->with('success', 'Certification renewed successfully.');
    }

    // ============================================================================
    // ANALYTICS & REPORTING
    // ============================================================================

    /**
     * Get user competency matrix
     */
    public function competencyMatrix(User $user)
    {
        $matrix = $this->trainingService->getUserCompetencyMatrix($user);

        return response()->json(['competency_matrix' => $matrix]);
    }

    /**
     * Identify skill gaps for user
     */
    public function skillGaps(User $user)
    {
        $gaps = $this->trainingService->identifySkillGaps($user);

        return response()->json(['skill_gaps' => $gaps]);
    }

    /**
     * Get training effectiveness
     */
    public function courseEffectiveness(TrainingCourse $course)
    {
        $effectiveness = $this->trainingService->getTrainingEffectiveness($course);

        return response()->json(['effectiveness' => $effectiveness]);
    }

    /**
     * Get renewal reminders
     */
    public function renewalReminders(Request $request)
    {
        $daysAhead = $request->input('days_ahead', 30);

        $reminders = $this->trainingService->getRenewalReminders($daysAhead);

        return response()->json(['reminders' => $reminders]);
    }

    /**
     * Calculate training ROI
     */
    public function trainingROI(TrainingCourse $course)
    {
        $roi = $this->trainingService->calculateTrainingROI($course);

        return response()->json(['roi' => $roi]);
    }
}
