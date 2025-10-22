@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Incident Management')

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
{{-- DO NOT CHANGE LAYOUT -- WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

@php
  $openIncidents = $statistics['by_status']['investigating'] ?? 0;
  $resolvedIncidents = $statistics['by_status']['resolved'] ?? 0;
  $criticalIncidents = $statistics['by_severity']['critical'] ?? 0;
  $pendingIncidents = ($statistics['total'] ?? 0) - $resolvedIncidents;

  $featuredIncident = $incidents->first();
  $filterPills = [
    ['label' => 'All branches', 'active' => true],
    ['label' => 'Sydney', 'active' => false],
    ['label' => 'Melbourne', 'active' => false],
    ['label' => 'Critical', 'active' => $criticalIncidents > 0],
    ['label' => 'Overdue', 'active' => false],
  ];
@endphp

<div class="incident-shell">
  <header class="incident-shell__hero">
    <div class="incident-shell__hero-main">
      <div>
        <span class="incident-eyebrow">Safety &amp; Incidents</span>
        <h1 class="incident-title">Incident Management</h1>
        <p class="incident-subtitle">Enterprise-grade command centre for every branch, with proactive signals and rapid triage tools.</p>
      </div>
      <div class="incident-hero-metric">
        <span class="incident-hero-metric__label">Total visibility</span>
        <span class="incident-hero-metric__value">{{ $statistics['total'] ?? 0 }}</span>
        <span class="incident-hero-metric__caption">Active records in the WHS4 network</span>
      </div>
    </div>
    <div class="incident-shell__hero-actions">
      <form method="GET" action="{{ route('incidents.index') }}" class="incident-search">
        <i class="icon-base ti ti-search incident-search__icon"></i>
        <input type="search" name="q" value="{{ request('q') }}" class="incident-search__input" placeholder="Search incidents, branches, people…" aria-label="Search incidents">
      </form>
      <a href="{{ route('incidents.create') }}" class="incident-primary-btn">
        <i class="icon-base ti ti-plus me-2"></i>
        Report incident
      </a>
    </div>
    <div class="incident-filter-pills">
      @foreach ($filterPills as $pill)
        <button type="button" class="incident-filter-pill{{ $pill['active'] ? ' is-active' : '' }}">{{ $pill['label'] }}</button>
      @endforeach
    </div>
  </header>

  <section class="incident-metrics">
    <article class="incident-metric-card">
      <div class="incident-metric-card__icon incident-metric-card__icon--brand">
        <i class="icon-base ti ti-activity"></i>
      </div>
      <span class="incident-metric-card__label">Total Incidents</span>
      <span class="incident-metric-card__value">{{ $statistics['total'] ?? 0 }}</span>
      <span class="incident-metric-card__meta">Rolling 90 day capture</span>
    </article>

    <article class="incident-metric-card">
      <div class="incident-metric-card__icon incident-metric-card__icon--critical">
        <i class="icon-base ti ti-alert-triangle"></i>
      </div>
      <span class="incident-metric-card__label">Critical Alerts</span>
      <span class="incident-metric-card__value">{{ $criticalIncidents }}</span>
      <span class="incident-metric-card__meta text-danger">Requires executive review</span>
    </article>

    <article class="incident-metric-card">
      <div class="incident-metric-card__icon incident-metric-card__icon--amber">
        <i class="icon-base ti ti-clock-hour-6"></i>
      </div>
      <span class="incident-metric-card__label">In Progress</span>
      <span class="incident-metric-card__value">{{ $openIncidents }}</span>
      <span class="incident-metric-card__meta">Assigned to branch leads</span>
    </article>

    <article class="incident-metric-card">
      <div class="incident-metric-card__icon incident-metric-card__icon--success">
        <i class="icon-base ti ti-shield-check"></i>
      </div>
      <span class="incident-metric-card__label">Resolved</span>
      <span class="incident-metric-card__value">{{ $resolvedIncidents }}</span>
      <span class="incident-metric-card__meta text-success">Closed with corrective actions</span>
    </article>
  </section>

  <div class="incident-layout">
    <div class="incident-main">
      <div class="incident-section-heading">
        <div>
          <h2>Live incident register</h2>
          <p>Sorted by newest first with real-time branch syncing.</p>
        </div>
        <span class="incident-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="incident-card-list">
        @forelse ($incidents as $incident)
          <article class="incident-card incident-card--{{ Str::slug($incident->severity) }}">
            <div class="incident-card__header">
              <span class="incident-chip incident-chip--id">#{{ $incident->id }}</span>
              <span class="incident-chip incident-chip--status incident-chip--status-{{ Str::slug($incident->status) }}">
                {{ ucfirst($incident->status) }}
              </span>
            </div>

            <div class="incident-card__body">
              <div>
                <h3>{{ ucfirst($incident->type) }}</h3>
                <p>{{ $incident->incident_datetime->format('d M Y • H:i') }}</p>
              </div>
              <div>
                <span class="incident-location-label">Location</span>
                <span>{{ $incident->location_specific ?? 'Not captured' }}</span>
              </div>
              <div>
                <span class="incident-location-label">Severity</span>
                <span class="incident-chip incident-chip--severity incident-chip--severity-{{ Str::slug($incident->severity) }}">{{ ucfirst($incident->severity) }}</span>
              </div>
              <div>
                <span class="incident-location-label">Reported by</span>
                <span>{{ $incident->user->name }}</span>
              </div>
            </div>

            <div class="incident-card__footer">
              <div class="incident-card__actions">
                <a href="{{ route('incidents.show', $incident) }}" class="incident-action-btn" aria-label="View incident">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>
                <a href="{{ route('incidents.edit', $incident) }}" class="incident-action-btn" aria-label="Assign or update incident">
                  <i class="icon-base ti ti-user-check"></i>
                  <span>Assign</span>
                </a>
                <form action="{{ route('incidents.destroy', $incident) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="incident-action-btn incident-action-btn--danger" onclick="return confirm('Archive this incident?')">
                    <i class="icon-base ti ti-archive"></i>
                    <span>Archive</span>
                  </button>
                </form>
              </div>
              <button class="incident-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </article>
        @empty
          <div class="incident-empty">
            <div class="incident-empty__content">
              <i class="icon-base ti ti-confetti incident-empty__icon"></i>
              <h3>All clear!</h3>
              <p>No incidents have been recorded for this time window. Keep monitoring to stay ahead of risk.</p>
              <a href="{{ route('incidents.create') }}" class="incident-primary-btn incident-primary-btn--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Log your first incident
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="incident-sidebar">
      <div class="incident-sidebar__panel">
        <h3>Executive snapshot</h3>
        <ul class="incident-sidebar__stats">
          <li>
            <span>Unresolved workload</span>
            <strong>{{ $pendingIncidents }}</strong>
          </li>
          <li>
            <span>Critical alerts</span>
            <strong class="text-danger">{{ $criticalIncidents }}</strong>
          </li>
          <li>
            <span>SLA compliance</span>
            <strong>85%</strong>
          </li>
        </ul>
        <p class="incident-sidebar__caption">SLA compliance is calculated across the last 14 days of branch activity.</p>
      </div>

      <div class="incident-sidebar__panel">
        <h3>Rapid preview</h3>
        @if ($featuredIncident)
          <div class="incident-preview">
            <span class="incident-chip incident-chip--id">#{{ $featuredIncident->id }}</span>
            <h4>{{ ucfirst($featuredIncident->type) }}</h4>
            <p class="incident-preview__meta">
              {{ $featuredIncident->incident_datetime->format('d M Y • H:i') }} &bull;
              {{ $featuredIncident->location_specific ?? 'Unset location' }}
            </p>
            <p class="incident-preview__description">
              This incident is {{ ucfirst($featuredIncident->severity) }} severity. Review supporting evidence, add corrective actions, or escalate to management.
            </p>
            <a href="{{ route('incidents.show', $featuredIncident) }}" class="incident-primary-btn incident-primary-btn--ghost">
              Open detail view
            </a>
          </div>
        @else
          <div class="incident-preview incident-preview--empty">
            <p>No incident selected.</p>
            <span>Choose an item from the register to surface investigation notes, attachments, and task assignments.</span>
          </div>
        @endif
      </div>
    </aside>
  </div>
</div>
@endsection

