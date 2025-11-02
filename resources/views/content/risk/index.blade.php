@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Risk Assessment')

@php
  use Illuminate\Support\Str;
@endphp

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
  $criticalRisks = $statistics['red'] ?? 0;
  $highRisks = $statistics['orange'] ?? 0;
  $mediumRisks = $statistics['yellow'] ?? 0;
  $lowRisks = $statistics['green'] ?? 0;

  $filterPills = [
    ['label' => 'All categories', 'active' => true],
    ['label' => 'Critical (20-25)', 'active' => $criticalRisks > 0],
    ['label' => 'High (12-19)', 'active' => $highRisks > 0],
    ['label' => 'Controlled', 'active' => false],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Safety & Risk"
    title="Risk Assessment"
    subtitle="Comprehensive 5×5 risk matrix with proactive hazard identification and control measures across all branches."
    :metric="true"
    metricLabel="Active assessments"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Live risk register across WHS4 network"
    :searchRoute="route('risk.index')"
    searchPlaceholder="Search risks, tasks, categories…"
    :createRoute="route('risk.create')"
    createLabel="Create risk assessment"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-shield-check"
      iconVariant="brand"
      label="Total Risks"
      :value="$statistics['total'] ?? 0"
      meta="Live risk register"
    />

    <x-whs.metric-card
      icon="ti-alert-octagon"
      iconVariant="critical"
      label="Critical Risks"
      :value="$criticalRisks"
      meta="Requires immediate action"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="warning"
      label="High Risks"
      :value="$highRisks"
      meta="Under active management"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Low Risks"
      :value="$lowRisks"
      meta="Controlled to acceptable levels"
      metaClass="text-success"
    />
  </section>

  <div class="whs-layout whs-layout--full-width">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Risk register</h2>
          <p>5×5 matrix assessments sorted by initial risk score (highest first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      {{-- Dense Table View (Default) --}}
      @include('content.risk._table-view')

      {{-- Old Card View (Deprecated) --}}
      <div class="whs-card-list" style="display: none;">
        @forelse ($risks as $risk)
          @php
            $riskScore = $risk->initial_risk_score;
            $severity = $riskScore >= 20 ? 'critical' : ($riskScore >= 12 ? 'high' : ($riskScore >= 6 ? 'medium' : 'low'));
            $severityLabel = $riskScore >= 20 ? 'Critical' : ($riskScore >= 12 ? 'High' : ($riskScore >= 6 ? 'Medium' : 'Low'));
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">#{{ $risk->id }}</span>
              <span class="whs-chip whs-chip--status">
                {{ ucwords(str_replace('-', ' ', $risk->category)) }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $risk->task_description }}</h3>
                <p>{{ $risk->created_at->format('d M Y') }}</p>
              </div>
              <div>
                <span class="whs-location-label">Risk Score</span>
                <span style="font-size: 1.5rem; font-weight: 700; color: var(--whs-slate-900);">{{ $riskScore }}</span>
              </div>
              <div>
                <span class="whs-location-label">Risk Level</span>
                <span class="whs-chip whs-chip--severity whs-chip--severity-{{ $severity }}">{{ $severityLabel }}</span>
              </div>
              <div>
                <span class="whs-location-label">Likelihood × Consequence</span>
                <span>{{ $risk->initial_likelihood }} × {{ $risk->initial_consequence }}</span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('risk.show', $risk) }}" class="whs-action-btn" aria-label="View risk assessment">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>
                <a href="{{ route('risk.edit', $risk) }}" class="whs-action-btn" aria-label="Edit risk assessment">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>
                <form action="{{ route('risk.destroy', $risk) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this risk assessment?')">
                    <i class="icon-base ti ti-trash"></i>
                    <span>Delete</span>
                  </button>
                </form>
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-shield-check whs-empty__icon"></i>
              <h3>No risk assessments yet</h3>
              <p>Start building your risk register by creating your first 5×5 matrix assessment.</p>
              <a href="{{ route('risk.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Create first assessment
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Risk matrix summary">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Critical (20-25)</span>
            <strong class="text-danger">{{ $criticalRisks }}</strong>
          </li>
          <li>
            <span>High (12-19)</span>
            <strong class="text-warning">{{ $highRisks }}</strong>
          </li>
          <li>
            <span>Medium (6-11)</span>
            <strong>{{ $mediumRisks }}</strong>
          </li>
          <li>
            <span>Low (1-5)</span>
            <strong class="text-success">{{ $lowRisks }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Risk scores calculated from 5×5 matrix: Likelihood (1-5) × Consequence (1-5).
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Risk control hierarchy">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Elimination</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Remove hazard completely</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Substitution</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Replace with safer alternative</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Engineering Controls</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Isolate people from hazard</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Administrative Controls</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Change work procedures</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. PPE</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Personal protective equipment</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection

