@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Training Management')

@section('page-script')
<script>
  (function ensureWideLayout() {
    var expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);
    document.cookie = 'contentLayout=wide;path=/;expires=' + expires.toUTCString();
  })();
</script>
@endsection

@section('content')
@include('layouts.sections.flash-message')

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Learning & Development"
    title="Training Management"
    subtitle="Centralized training administration with course management, progress tracking, and certification oversight for workforce compliance."
    :metric="true"
    metricLabel="Active certifications"
    :metricValue="0"
    metricCaption="Valid certifications"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-book"
      iconVariant="brand"
      label="Training Courses"
      :value="0"
      meta="Active courses available"
    />

    <x-whs.metric-card
      icon="ti-clock"
      iconVariant="info"
      label="Active Training"
      :value="0"
      meta="In progress"
      metaClass="text-info"
    />

    <x-whs.metric-card
      icon="ti-certificate"
      iconVariant="success"
      label="Active Certifications"
      :value="0"
      meta="Valid certifications"
      metaClass="text-success"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Training modules</h2>
          <p>Access course management, training records, and certification tracking.</p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Training Courses Module -->
        <div class="col-lg-4">
          <x-whs.card severity="low">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">
                <i class="icon-base ti ti-book-2 me-1"></i>
                Courses
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>Training Courses</h3>
                <p>Course catalog & management</p>
              </div>
              <div>
                <p style="margin-bottom: 1rem; color: var(--whs-slate-600); font-size: 0.875rem;">Create and manage training courses, set requirements, track delivery methods, and manage course content.</p>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Safety induction courses</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Driver training programs</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Equipment operation training</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Compliance training</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('training.courses.index') }}" class="whs-action-btn" aria-label="Manage courses">
                  <i class="icon-base ti ti-book"></i>
                  <span>Manage Courses</span>
                </a>
              </div>
            </div>
          </x-whs.card>
        </div>

        <!-- Training Records Module -->
        <div class="col-lg-4">
          <x-whs.card severity="low">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">
                <i class="icon-base ti ti-clipboard-text me-1"></i>
                Records
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>Training Records</h3>
                <p>Employee training tracking</p>
              </div>
              <div>
                <p style="margin-bottom: 1rem; color: var(--whs-slate-600); font-size: 0.875rem;">Assign training to employees, track progress, record completion, manage assessments, and monitor compliance.</p>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Assign training to staff</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Track completion progress</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Record assessment scores</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Monitor overdue training</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('training.records.index') }}" class="whs-action-btn" aria-label="View training records">
                  <i class="icon-base ti ti-clipboard-check"></i>
                  <span>View Records</span>
                </a>
              </div>
            </div>
          </x-whs.card>
        </div>

        <!-- Certifications Module -->
        <div class="col-lg-4">
          <x-whs.card severity="low">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">
                <i class="icon-base ti ti-certificate me-1"></i>
                Certifications
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>Certifications</h3>
                <p>License & certificate management</p>
              </div>
              <div>
                <p style="margin-bottom: 1rem; color: var(--whs-slate-600); font-size: 0.875rem;">Manage employee certifications, licenses, track expiry dates, verify credentials, and manage renewals.</p>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Driver licenses & endorsements</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Forklift & equipment licenses</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Safety certifications</span>
                  </li>
                  <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    <i class="icon-base ti ti-check" style="color: var(--whs-success);"></i>
                    <span>Expiry tracking & alerts</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('training.certifications.index') }}" class="whs-action-btn" aria-label="Manage certifications">
                  <i class="icon-base ti ti-award"></i>
                  <span>Manage Certifications</span>
                </a>
              </div>
            </div>
          </x-whs.card>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="whs-section-heading mt-4">
        <div>
          <h2>Quick actions</h2>
          <p>Frequently used training management operations.</p>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-3 col-sm-6">
          <a href="{{ route('training.courses.index') }}" class="btn btn-outline-primary w-100">
            <i class="icon-base ti ti-plus me-2"></i> Create Course
          </a>
        </div>
        <div class="col-md-3 col-sm-6">
          <a href="{{ route('training.records.index') }}" class="btn btn-outline-primary w-100">
            <i class="icon-base ti ti-user-plus me-2"></i> Assign Training
          </a>
        </div>
        <div class="col-md-3 col-sm-6">
          <a href="{{ route('training.certifications.index') }}" class="btn btn-outline-primary w-100">
            <i class="icon-base ti ti-shield-check me-2"></i> Add Certification
          </a>
        </div>
        <div class="col-md-3 col-sm-6">
          <a href="{{ route('training.records.index') }}?overdue=1" class="btn btn-outline-warning w-100">
            <i class="icon-base ti ti-alert-triangle me-2"></i> View Overdue
          </a>
        </div>
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Training system">
        <p class="whs-sidebar__caption" style="margin-bottom: 1rem;">
          This system helps you maintain compliance with training requirements, track employee competencies, and manage certifications.
        </p>
        <ul class="whs-sidebar__stats">
          <li>
            <span>Course Management</span>
            <strong>Create & assign courses</strong>
          </li>
          <li>
            <span>Progress Tracking</span>
            <strong>Monitor completion</strong>
          </li>
          <li>
            <span>Certification Oversight</span>
            <strong>Track expiry dates</strong>
          </li>
        </ul>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Training types">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Safety Induction</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Mandatory workplace safety orientation</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Driver Training</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Vehicle operation and road safety</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Equipment Operation</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Machinery and equipment certification</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Compliance Training</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Regulatory and policy compliance</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

@endsection

