@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'WHS4 Dashboard')

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const style = getComputedStyle(document.documentElement);
  const resolveVar = (token, fallback) => {
    const value = style.getPropertyValue(token);
    return value && value.trim() !== '' ? value.trim() : fallback;
  };

  const accent = resolveVar('--sensei-accent', '#3b82f6');
  const textSecondary = resolveVar('--sensei-text-secondary', '#9ca3af');
  const gridColor = resolveVar('--sensei-gridline', 'rgba(255, 255, 255, 0.08)');
  const success = resolveVar('--sensei-success', '#5eead4');
  const warning = resolveVar('--sensei-warning', '#fbbf24');
  const high = resolveVar('--sensei-alert', '#fb7185');
  const alert = resolveVar('--sensei-alert', '#ef4444');
  const tooltipTheme = document.documentElement.dataset.bsTheme === 'light' ? 'light' : 'dark';

  const incidentTrendChart = document.querySelector('#incidentTrendChart');
  if (incidentTrendChart) {
    const monthlyData = @json($monthlyIncidents);
    const months = monthlyData.map(item => item.month);
    const counts = monthlyData.map(item => item.count);

    const options = {
      series: [{ name: 'Incidents', data: counts }],
      chart: { type: 'line', height: 300, toolbar: { show: false } },
      colors: [accent],
      stroke: { curve: 'smooth', width: 3 },
      xaxis: {
        categories: months,
        labels: { style: { colors: textSecondary } },
        axisBorder: { color: gridColor },
        axisTicks: { color: gridColor }
      },
      yaxis: {
        labels: { style: { colors: textSecondary } }
      },
      grid: { borderColor: gridColor, strokeDashArray: 5 },
      tooltip: {
        theme: tooltipTheme,
        y: { formatter: val => `${val} incidents` }
      }
    };

    new ApexCharts(incidentTrendChart, options).render();
  }

  const riskDonutChart = document.querySelector('#riskDonutChart');
  if (riskDonutChart) {
    const riskData = @json($riskDistribution);

    const options = {
      series: [riskData.low, riskData.medium, riskData.high, riskData.critical],
      labels: ['Low (1-5)', 'Medium (6-11)', 'High (12-19)', 'Critical (20-25)'],
      chart: { type: 'donut', height: 300 },
      colors: [success, warning, high, alert],
      legend: { position: 'bottom', labels: { colors: textSecondary } },
      dataLabels: {
        enabled: true,
        formatter: val => `${Math.round(val)}%`
      },
      tooltip: {
        theme: tooltipTheme,
        y: { formatter: val => `${val} risks` }
      }
    };

    new ApexCharts(riskDonutChart, options).render();
  }
});
</script>
@endsection

@section('content')
@include('layouts.sections.flash-message')

@php
    $safetyScore = $stats['total_incidents'] > 0 ? max(0, 100 - ($stats['open_incidents'] * 10)) : 100;
    $openIncidentsLabel = $stats['open_incidents'] === 1 ? 'case' : 'cases';
    $openCaption = 'Based on ' . $stats['open_incidents'] . ' open ' . ($stats['open_incidents'] === 1 ? 'incident' : 'incidents');
    $overdueInspectionsLabel = $stats['inspections_overdue'] === 1 ? 'inspection' : 'inspections';
    $incidentTrend = $stats['incidents_trend'] ?? 0;
    $riskTrend = $stats['risks_trend'] ?? 0;
    $vehicleTrend = $stats['vehicles_trend'] ?? 0;
    $inspectionTrend = $stats['inspections_trend'] ?? 0;
    $activeAlerts = $stats['active_emergency_alerts'] ?? 0;
    $isEmployee = auth()->user()?->hasRole('Employee') ?? false;
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Safety Dashboard"
    title="WHS4 Overview"
    subtitle="Real-time workplace health and safety monitoring across all operations"
    :metric="true"
    metric-label="Safety Score"
    :metric-value="$safetyScore . '%'"
    :metric-caption="$openCaption"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Total Incidents"
      :value="$stats['total_incidents']"
      :meta="$stats['open_incidents'] . ' open ' . $openIncidentsLabel"
    />
    <x-whs.metric-card
      icon="ti-shield-exclamation"
      iconVariant="warning"
      label="High Risk Items"
      :value="$stats['high_risk_assessments']"
      meta="Requires immediate attention"
    />
    <x-whs.metric-card
      icon="ti-truck"
      iconVariant="success"
      label="Active Vehicles"
      :value="$stats['vehicles_active']"
      :meta="'of ' . $stats['vehicles_total'] . ' in fleet'"
    />
    <x-whs.metric-card
      icon="ti-clipboard-check"
      iconVariant="brand"
      label="Pending Inspections"
      :value="$stats['inspections_pending']"
      :meta="$stats['inspections_overdue'] . ' overdue ' . $overdueInspectionsLabel"
    />
  </section>

  @if($activeAlerts > 0)
    <x-whs.card severity="critical">
      <div class="sensei-alert">
        <div class="sensei-alert__header">
          <div>
            <span class="section-eyebrow">System Notice</span>
            <h2>Active Emergency Alert</h2>
          </div>
          <span class="whs-chip whs-chip--status-critical">
            <i class="icon-base ti ti-alarm"></i>
            {{ $activeAlerts }} {{ $activeAlerts === 1 ? 'alert' : 'alerts' }}
          </span>
        </div>
        <p class="sensei-alert__body">
          Immediate action required. Coordinate with response teams to resolve the active incident workflow.
        </p>
        <div class="sensei-alert__actions">
          <a href="{{ route('emergency.index') }}" class="whs-btn-primary">
            <i class="icon-base ti ti-external-link"></i>
            Review Alerts
          </a>
          <a href="{{ route('incidents.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
            <i class="icon-base ti ti-clipboard-plus"></i>
            Log Follow-up
          </a>
        </div>
      </div>
    </x-whs.card>
  @endif

  @if($userVehicleAssignment && $userVehicleAssignment->vehicle)
    <x-whs.card severity="info">
      <div class="sensei-alert">
        <div class="sensei-alert__header">
          <div>
            <span class="section-eyebrow">Your Assigned Vehicle</span>
            <h2>{{ $userVehicleAssignment->vehicle->make }} {{ $userVehicleAssignment->vehicle->model }} ({{ $userVehicleAssignment->vehicle->year }})</h2>
          </div>
          <span class="whs-chip whs-chip--status-brand">
            <i class="icon-base ti ti-car"></i>
            {{ $userVehicleAssignment->vehicle->registration_state ? $userVehicleAssignment->vehicle->registration_state . ' · ' : '' }}
            {{ $userVehicleAssignment->vehicle->registration_number }}
          </span>
        </div>
        <p class="sensei-alert__body">
          Daily pre-trip inspection required before operation. Current odometer: {{ number_format($userVehicleAssignment->vehicle->odometer_reading ?? 0) }} km.
          @if($userVehicleAssignment->vehicle->inspection_due_date && $userVehicleAssignment->vehicle->isInspectionDue())
            <strong class="text-danger">Inspection due: {{ $userVehicleAssignment->vehicle->inspection_due_date->format('d/m/Y') }}</strong>
          @endif
        </p>
        <div class="sensei-alert__actions">
          <a href="{{ route('driver.vehicle-inspections.create') }}" class="whs-btn-primary">
            <i class="icon-base ti ti-clipboard-check"></i>
            Start Daily Inspection
          </a>
          <a href="{{ route('vehicles.show', $userVehicleAssignment->vehicle) }}" class="whs-btn-primary whs-btn-primary--ghost">
            <i class="icon-base ti ti-eye"></i>
            Vehicle Details
          </a>
        </div>
      </div>
    </x-whs.card>
  @elseif($userVehicleAssignment)
    <x-whs.card severity="info">
      <div class="sensei-alert">
        <div class="sensei-alert__header">
          <div>
            <span class="section-eyebrow">Your Assigned Vehicle</span>
            <h2>Vehicle record unavailable</h2>
          </div>
        </div>
        <p class="sensei-alert__body">The vehicle linked to your assignment is no longer available. Please contact your fleet administrator.</p>
      </div>
    </x-whs.card>
  @endif

  <div class="whs-main">
    <div class="sensei-panel-grid">
      <x-whs.card>
        <div class="whs-section-heading">
          <div>
            <h2>Incident Trend</h2>
            <p>Six-month trend of recorded incidents across the organization.</p>
          </div>
        </div>
        <div id="incidentTrendChart" class="sensei-chart"></div>
      </x-whs.card>

      <x-whs.card>
        <div class="whs-section-heading">
          <div>
            <h2>Risk Distribution</h2>
            <p>Breakdown of current risk ratings across operational areas.</p>
          </div>
        </div>
        <div id="riskDonutChart" class="sensei-chart sensei-chart--centered"></div>
      </x-whs.card>
    </div>

    <div class="sensei-panel-grid">
      <x-whs.card>
        <div class="whs-section-heading">
          <div>
            <h2>Recent Incidents</h2>
            <p>Latest activity logged by branch teams.</p>
          </div>
          <a href="{{ route('incidents.index') }}" class="whs-btn-primary whs-btn-primary--ghost">
            <i class="icon-base ti ti-external-link"></i>
            View All
          </a>
        </div>
        <div class="sensei-table-wrapper">
          <table class="sensei-table">
            <thead>
              <tr>
                <th scope="col">Type</th>
                <th scope="col">Severity</th>
                <th scope="col">Date</th>
                <th scope="col">Location</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentIncidents as $incident)
                <tr>
                  <td>
                    <a href="{{ route('incidents.show', $incident) }}" class="sensei-link">
                      {{ ucwords(str_replace('_', ' ', $incident->incident_type)) }}
                    </a>
                  </td>
                  <td>
                    @switch($incident->severity)
                      @case('critical')
                        <span class="whs-chip whs-chip--status-critical">Critical</span>
                        @break
                      @case('high')
                        <span class="whs-chip whs-chip--severity-high">High</span>
                        @break
                      @case('medium')
                        <span class="whs-chip whs-chip--severity-medium">Medium</span>
                        @break
                      @case('low')
                        <span class="whs-chip whs-chip--severity-low">Low</span>
                        @break
                    @endswitch
                  </td>
                  <td>{{ $incident->incident_datetime->format('d/m/Y') }}</td>
                  <td>{{ $incident->location ?? 'Not specified' }}</td>
                  <td>
                    @switch($incident->status)
                      @case('open')
                        <span class="whs-chip whs-chip--status-critical">Open</span>
                        @break
                      @case('investigating')
                        <span class="whs-chip whs-chip--status-warning">Investigating</span>
                        @break
                      @case('resolved')
                        <span class="whs-chip whs-chip--status-active">Resolved</span>
                        @break
                      @case('closed')
                        <span class="whs-chip whs-chip--status">Closed</span>
                        @break
                    @endswitch
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5">
                    <div class="whs-empty">
                      <div class="whs-empty__icon">
                        <i class="icon-base ti ti-shield-check"></i>
                      </div>
                      <h3>No recent incidents</h3>
                      <p>All operations are running smoothly.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </x-whs.card>

      <x-whs.card class="sensei-panel--actions">
        <div class="whs-section-heading">
          <div>
            <h2>Quick Actions</h2>
            <p>Rapid actions for your safety operations team.</p>
          </div>
        </div>

        <div class="sensei-action-list">
          <a href="{{ route('incidents.create') }}" class="sensei-action sensei-action--danger">
            <span class="sensei-action__label">
              <i class="icon-base ti ti-plus"></i>
              Report Incident
            </span>
            <span class="sensei-action__meta">Log a new incident record</span>
          </a>

          @if($isEmployee)
            <a href="{{ route('driver.vehicle-inspections.create') }}" class="sensei-action sensei-action--info">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-clipboard-check"></i>
                Start Vehicle Inspection
              </span>
              <span class="sensei-action__meta">Complete your daily checklist</span>
            </a>
          @else
            <a href="{{ route('risk.create') }}" class="sensei-action sensei-action--warning">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-shield"></i>
                Create Risk Assessment
              </span>
              <span class="sensei-action__meta">Update current risk register</span>
            </a>
            <a href="{{ route('emergency.create') }}" class="sensei-action sensei-action--danger">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-alarm"></i>
                Trigger Emergency Alert
              </span>
              <span class="sensei-action__meta">Notify response teams instantly</span>
            </a>
            <a href="{{ route('inspections.create') }}" class="sensei-action sensei-action--info">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-clipboard"></i>
                New Inspection
              </span>
              <span class="sensei-action__meta">Schedule a site walkthrough</span>
            </a>
            <a href="{{ route('vehicles.index') }}" class="sensei-action sensei-action--success">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-car"></i>
                Manage Vehicles
              </span>
              <span class="sensei-action__meta">Monitor fleet readiness</span>
            </a>
          @endif
        </div>

        <span class="section-eyebrow">System Status</span>
        <ul class="sensei-status-list">
          <li class="sensei-status-item">
            <span>Emergency Alerts</span>
            <span class="sensei-badge {{ $activeAlerts > 0 ? 'sensei-badge--danger' : 'sensei-badge--success' }}">
              {{ $activeAlerts > 0 ? $activeAlerts . ' active' : 'All clear' }}
            </span>
          </li>
          <li class="sensei-status-item">
            <span>Open Incidents</span>
            <span class="sensei-badge {{ $stats['open_incidents'] > 0 ? 'sensei-badge--warning' : 'sensei-badge--success' }}">
              {{ $stats['open_incidents'] }}
            </span>
          </li>
          <li class="sensei-status-item">
            <span>High Risks</span>
            <span class="sensei-badge {{ $stats['high_risk_assessments'] > 0 ? 'sensei-badge--warning' : 'sensei-badge--success' }}">
              {{ $stats['high_risk_assessments'] }}
            </span>
          </li>
          <li class="sensei-status-item">
            <span>Overdue Inspections</span>
            <span class="sensei-badge {{ $stats['inspections_overdue'] > 0 ? 'sensei-badge--danger' : 'sensei-badge--success' }}">
              {{ $stats['inspections_overdue'] }}
            </span>
          </li>
        </ul>
      </x-whs.card>
    </div>
  </div>
</div>

@endsection
