@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Emergency Response')

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
  $activeAlerts = $statistics['active'] ?? 0;
  $respondedAlerts = $statistics['responded'] ?? 0;
  $resolvedAlerts = $statistics['resolved'] ?? 0;

  $filterPills = [
    ['label' => 'All alerts', 'active' => true],
    ['label' => 'Active', 'active' => $activeAlerts > 0],
    ['label' => 'Responded', 'active' => $respondedAlerts > 0],
    ['label' => 'Resolved', 'active' => false],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Safety & Emergency"
    title="Emergency Response"
    subtitle="Real-time emergency alert system with 5-second panic button, GPS tracking, and instant responder coordination across all branches."
    :metric="true"
    metricLabel="Total alerts"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Emergency alert registry across WHS4 network"
    :searchRoute="route('emergency.index')"
    searchPlaceholder="Search alerts, responders, locations…"
    :createRoute="route('emergency.create')"
    createLabel="Trigger emergency alert"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-alarm-minus"
      iconVariant="brand"
      label="Total Alerts"
      :value="$statistics['total'] ?? 0"
      meta="All emergency events"
    />

    <x-whs.metric-card
      icon="ti-alert-octagon"
      iconVariant="critical"
      label="Active Alerts"
      :value="$activeAlerts"
      meta="Requires immediate response"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-user-check"
      iconVariant="warning"
      label="Responded"
      :value="$respondedAlerts"
      meta="Responder en route"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Resolved"
      :value="$resolvedAlerts"
      meta="Safely resolved events"
      metaClass="text-success"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Emergency alert register</h2>
          <p>Real-time emergency events sorted by trigger time (newest first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        @forelse ($alerts as $alert)
          @php
            $severity = $alert->status === 'triggered' ? 'critical' : ($alert->status === 'responded' ? 'high' : 'low');
            $severityLabel = $alert->status === 'triggered' ? 'Active' : ($alert->status === 'responded' ? 'Responded' : 'Resolved');
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">#{{ substr($alert->id, 0, 8) }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($severityLabel) }}">
                {{ $severityLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ ucfirst($alert->type) }} Alert</h3>
                <p>{{ $alert->triggered_at->format('d M Y • H:i') }}</p>
              </div>
              <div>
                <span class="whs-location-label">Triggered By</span>
                <span>{{ $alert->user->name }}</span>
              </div>
              <div>
                <span class="whs-location-label">Location</span>
                <span>{{ $alert->location_description ?? 'No location' }}</span>
              </div>
              <div>
                <span class="whs-location-label">Responder</span>
                <span>{{ $alert->responder?->name ?? '-' }}</span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('emergency.show', $alert) }}" class="whs-action-btn" aria-label="View emergency alert">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                @if($alert->status === 'triggered')
                  <form action="{{ route('emergency.respond', $alert) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--warning">
                      <i class="icon-base ti ti-user-check"></i>
                      <span>Respond</span>
                    </button>
                  </form>
                @endif

                @if(in_array($alert->status, ['triggered', 'responded']))
                  <button type="button" class="whs-action-btn whs-action-btn--success" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $alert->id }}">
                    <i class="icon-base ti ti-check"></i>
                    <span>Resolve</span>
                  </button>
                @endif

                <a href="{{ route('emergency.edit', $alert) }}" class="whs-action-btn" aria-label="Edit emergency alert">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                <form action="{{ route('emergency.destroy', $alert) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this emergency alert?')">
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

          <!-- Resolve Modal -->
          <div class="modal fade" id="resolveModal{{ $alert->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form action="{{ route('emergency.resolve', $alert) }}" method="POST">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Resolve Emergency Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="response_notes{{ $alert->id }}" class="form-label">Resolution Notes</label>
                      <textarea id="response_notes{{ $alert->id }}" name="response_notes" class="form-control" rows="4" required></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Resolve Alert</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-alarm-minus whs-empty__icon"></i>
              <h3>No emergency alerts</h3>
              <p>No emergency events have been triggered yet. The panic button is available on all mobile devices.</p>
              <a href="{{ route('emergency.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-alarm me-2"></i>
                Trigger emergency alert
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Emergency response protocol">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active (Triggered)</span>
            <strong class="text-danger">{{ $activeAlerts }}</strong>
          </li>
          <li>
            <span>Responded (En route)</span>
            <strong class="text-warning">{{ $respondedAlerts }}</strong>
          </li>
          <li>
            <span>Resolved (Completed)</span>
            <strong class="text-success">{{ $resolvedAlerts }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Emergency alerts require immediate attention. Active alerts should receive response within 5 minutes.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="5-second panic button">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(255, 244, 244, 0.96), rgba(255, 228, 228, 0.98)); border-radius: 12px; border: 1px solid rgba(234, 84, 85, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Hold Button</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Press and hold for 5 seconds</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. GPS Broadcast</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Location sent to all nearby personnel</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Auto-Notify</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Emergency contacts receive alert</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Two-Way Comm</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Voice communication established</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Response Team</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Nearest responder dispatched</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection

