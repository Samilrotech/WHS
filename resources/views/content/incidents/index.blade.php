@extends('layouts.layoutMaster')

@section('title', 'Incident Management')

@section('page-script')
<script>
  document.cookie = 'contentLayout=wide;path=/;max-age=' + 60 * 60 * 24 * 365;
</script>
@endsection

@section('content')
@include('layouts.sections.flash-message')

@php
  use Illuminate\Support\Str;

  $openIncidents = $statistics['by_status']['investigating'] ?? 0;
  $resolvedIncidents = $statistics['by_status']['resolved'] ?? 0;
  $criticalIncidents = $statistics['by_severity']['critical'] ?? 0;
  $pendingIncidents = max(0, ($statistics['total'] ?? 0) - $resolvedIncidents);
  $resolutionRate = ($statistics['total'] ?? 0) > 0
      ? round(($resolvedIncidents / $statistics['total']) * 100)
      : null;
  $featuredIncident = $incidents->first();
  $canEdit = auth()->user()?->can('edit incidents');
  $canAssign = auth()->user()?->can('assign incidents');
  $canDelete = auth()->user()?->can('delete incidents');
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Safety & Incidents"
    title="Incident Management"
    subtitle="Capture, track, and resolve frontline safety incidents with real-time visibility into branch activity."
    :metric="true"
    metric-label="Total visibility"
    :metric-value="$statistics['total'] ?? 0"
    metric-caption="Active records in the WHS4 network"
  >
    <x-slot:actions>
      <form method="GET" action="{{ route('incidents.index') }}" class="sensei-search sensei-search--hero" role="search">
        <i class="icon-base ti ti-search sensei-search__icon"></i>
        <input
          type="search"
          name="q"
          value="{{ request('q') }}"
          class="sensei-search__input"
          placeholder="Search incident ID, description, or location..."
          aria-label="Search incidents"
        >
      </form>
      <a href="{{ route('incidents.create') }}" class="incident-primary-btn">
        <i class="icon-base ti ti-plus me-2"></i>
        Report incident
      </a>
    </x-slot:actions>
  </x-whs.hero>

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-activity"
      iconVariant="brand"
      label="Total Incidents"
      :value="$statistics['total'] ?? 0"
      meta="Rolling 90 day capture"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Critical Alerts"
      :value="$criticalIncidents"
      meta="Requires review"
    />

    <x-whs.metric-card
      icon="ti-car"
      iconVariant="info"
      label="Assigned Vehicles"
      :value="$assignedVehiclesCount ?? 0"
      meta="Currently in use"
    />

    <x-whs.metric-card
      icon="ti-clock-hour-6"
      iconVariant="warning"
      label="In Progress"
      :value="$openIncidents"
      meta="Pending investigation"
    />

    <x-whs.metric-card
      icon="ti-shield-check"
      iconVariant="success"
      label="Resolved"
      :value="$resolvedIncidents"
      meta="Closed with actions"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Live incident register</h2>
          <p>Sorted by newest first and filtered using the search above.</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-list whs-list--staggered">
        @forelse ($incidents as $incident)
          <x-whs.card class="incident-card sensei-surface-card">
            <div class="incident-card__header">
              <span class="incident-chip incident-chip--id">#{{ $incident->id }}</span>
              <span class="incident-chip incident-chip--status incident-chip--status-{{ Str::slug($incident->status) }}">
                {{ ucfirst($incident->status) }}
              </span>
            </div>

            <div class="incident-card__body">
              <div>
                <h3 class="incident-card__title">{{ ucfirst($incident->type) }}</h3>
                <p class="incident-card__timestamp text-muted mb-0">
                  {{ $incident->incident_datetime->format('d M Y � H:i') }}
                </p>
              </div>
              <dl class="incident-data">
                <div class="incident-data__item">
                  <dt>Location</dt>
                  <dd>{{ $incident->location_specific ?? 'Not captured' }}</dd>
                </div>
                <div class="incident-data__item">
                  <dt>Severity</dt>
                  <dd>
                    <span class="incident-chip incident-chip--severity incident-chip--severity-{{ Str::slug($incident->severity) }}">
                      {{ ucfirst($incident->severity) }}
                    </span>
                  </dd>
                </div>
                <div class="incident-data__item">
                  <dt>Reported by</dt>
                  <dd>{{ $incident->user->name }}</dd>
                </div>
              </dl>
            </div>

            <div class="incident-card__footer">
              <div class="incident-card__actions">
                <a href="{{ route('incidents.show', $incident) }}" class="incident-action-btn" aria-label="View incident">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                @if($canEdit)
                  <a href="{{ route('incidents.edit', $incident) }}" class="incident-action-btn" aria-label="Update incident">
                    <i class="icon-base ti ti-edit"></i>
                    <span>Edit</span>
                  </a>
                @endif

                @if($canAssign)
                  <a href="{{ route('incidents.edit', $incident) }}" class="incident-action-btn" aria-label="Manage assignment">
                    <i class="icon-base ti ti-user-check"></i>
                    <span>Assign</span>
                  </a>
                @endif

                @if($canDelete)
                  <form action="{{ route('incidents.destroy', $incident) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="incident-action-btn incident-action-btn--danger" onclick="return confirm('Archive this incident?')">
                      <i class="icon-base ti ti-archive"></i>
                      <span>Archive</span>
                    </button>
                  </form>
                @endif
              </div>
            </div>
          </x-whs.card>
        @empty
          <x-whs.card class="incident-empty">
            <div class="incident-empty__content">
              <i class="icon-base ti ti-confetti incident-empty__icon"></i>
              <h3>All clear!</h3>
              <p>No incidents have been recorded for this time window. Keep monitoring to stay ahead of risk.</p>
              <a href="{{ route('incidents.create') }}" class="incident-primary-btn incident-primary-btn--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Log your first incident
              </a>
            </div>
          </x-whs.card>
        @endforelse
      </div>
    </div>

    <aside class="incident-sidebar">
      <x-whs.sidebar-panel>
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
            <span>Resolution rate</span>
            <strong>{{ $resolutionRate !== null ? $resolutionRate . '%' : '�' }}</strong>
          </li>
        </ul>
        <p class="incident-sidebar__caption">
          {{ $resolutionRate !== null
              ? 'Percentage of resolved incidents across this dataset.'
              : 'Resolution data will appear once incidents have been closed.' }}
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel>
        <h3>Rapid preview</h3>
        @if ($featuredIncident)
          <div class="incident-preview">
            <span class="incident-chip incident-chip--id">#{{ $featuredIncident->id }}</span>
            <h4>{{ ucfirst($featuredIncident->type) }}</h4>
            <p class="incident-preview__meta">
              {{ $featuredIncident->incident_datetime->format('d M Y � H:i') }} &bull;
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
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection
