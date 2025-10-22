@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Audit & Inspection')

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

@php
  $filterPills = [
    ['label' => 'All audits', 'active' => true],
    ['label' => 'Safety Inspections', 'active' => false],
    ['label' => 'Compliance Audits', 'active' => false],
    ['label' => 'Equipment Checks', 'active' => false],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Quality & Compliance"
    title="Audit & Inspection"
    subtitle="Safety inspections, compliance audits, and corrective actions with photo evidence capture and automated reporting across all branches."
    :metric="true"
    metricLabel="Total audits"
    :metricValue="0"
    metricCaption="Audit registry across WHS4 network"
    :searchRoute="route('audit.index')"
    searchPlaceholder="Search audits, checklists, findings…"
    :createRoute="route('audit.create')"
    createLabel="Create audit"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-checklist"
      iconVariant="brand"
      label="Total Audits"
      :value="0"
      meta="All inspections and audits"
    />

    <x-whs.metric-card
      icon="ti-shield-check"
      iconVariant="success"
      label="Compliant"
      :value="0"
      meta="Passed all requirements"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="warning"
      label="Non-Compliant"
      :value="0"
      meta="Requires corrective action"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-clock"
      iconVariant="critical"
      label="Overdue"
      :value="0"
      meta="Scheduled audits past due"
      metaClass="text-danger"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Audit register</h2>
          <p>Safety inspections and compliance audits sorted by date (most recent first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        <div class="whs-empty">
          <div class="whs-empty__content">
            <i class="icon-base ti ti-checklist whs-empty__icon"></i>
            <h3>No audits yet</h3>
            <p>No audits or inspections have been created. Start tracking your compliance and safety inspections.</p>
            <a href="{{ route('audit.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
              <i class="icon-base ti ti-plus me-2"></i>
              Create first audit
            </a>
          </div>
        </div>
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Audit workflow">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Compliant (Passed)</span>
            <strong class="text-success">0</strong>
          </li>
          <li>
            <span>Non-Compliant (Action Required)</span>
            <strong class="text-warning">0</strong>
          </li>
          <li>
            <span>Overdue (Past Schedule)</span>
            <strong class="text-danger">0</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Automated audit scheduling ensures compliance with safety regulations and quality standards.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Audit lifecycle">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Schedule</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Plan inspection checklist and scope</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Inspect</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Conduct on-site assessment</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Document</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Photo evidence and findings capture</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Corrective Action</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Assign and track remediation</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Report</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated compliance reporting</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection

