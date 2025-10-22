@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Vehicle Reporting Dashboard')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

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
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <span class="whs-eyebrow">Fleet Analytics</span>
      <h1 class="whs-page-title">Vehicle Reporting Dashboard</h1>
      <p class="whs-subtitle">Fleet performance metrics, inspection analytics, and maintenance trend analysis across all branches.</p>
    </div>

    <!-- Period Selector -->
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-outline-primary {{ $period === 'day' ? 'active' : '' }}" onclick="window.location.href='{{ route('vehicle-reporting.index', ['period' => 'day']) }}'">Day</button>
      <button type="button" class="btn btn-outline-primary {{ $period === 'week' ? 'active' : '' }}" onclick="window.location.href='{{ route('vehicle-reporting.index', ['period' => 'week']) }}'">Week</button>
      <button type="button" class="btn btn-outline-primary {{ $period === 'month' ? 'active' : '' }}" onclick="window.location.href='{{ route('vehicle-reporting.index', ['period' => 'month']) }}'">Month</button>
      <button type="button" class="btn btn-outline-primary {{ $period === 'quarter' ? 'active' : '' }}" onclick="window.location.href='{{ route('vehicle-reporting.index', ['period' => 'quarter']) }}'">Quarter</button>
      <button type="button" class="btn btn-outline-primary {{ $period === 'year' ? 'active' : '' }}" onclick="window.location.href='{{ route('vehicle-reporting.index', ['period' => 'year']) }}'">Year</button>
    </div>
  </div>

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-car"
      iconVariant="brand"
      label="Total Vehicles"
      :value="$metrics['fleet_overview']['total_vehicles'] ?? 0"
      meta="Complete fleet inventory"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active"
      :value="$metrics['fleet_overview']['active'] ?? 0"
      meta="Operational and ready"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-tool"
      iconVariant="warning"
      label="In Maintenance"
      :value="$metrics['fleet_overview']['in_maintenance'] ?? 0"
      meta="Currently being serviced"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-trending-up"
      iconVariant="success"
      label="Availability Rate"
      :value="number_format($metrics['fleet_overview']['availability_rate'] ?? 0, 1) . '%'"
      meta="Fleet availability"
      metaClass="text-info"
    />
  </section>

  <!-- Quick Links to Sub-Dashboards -->
  <div class="whs-section-heading">
    <div>
      <h2>Analytics dashboards</h2>
      <p>Access detailed analytics across fleet, inspections, maintenance, costs, and compliance.</p>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <a href="{{ route('vehicle-reporting.fleet', ['period' => $period]) }}" class="whs-card whs-card--link">
        <div class="whs-card__body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h3 class="mb-1">Fleet Overview</h3>
              <p class="text-muted mb-0" style="font-size: 0.875rem;">View detailed fleet statistics</p>
            </div>
            <i class="icon-base ti ti-arrow-right" style="font-size: 1.5rem; color: var(--whs-brand);"></i>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="{{ route('vehicle-reporting.inspections', ['period' => $period]) }}" class="whs-card whs-card--link">
        <div class="whs-card__body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h3 class="mb-1">Inspection Analytics</h3>
              <p class="text-muted mb-0" style="font-size: 0.875rem;">Monitor inspection performance</p>
            </div>
            <i class="icon-base ti ti-arrow-right" style="font-size: 1.5rem; color: var(--whs-brand);"></i>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="{{ route('vehicle-reporting.maintenance', ['period' => $period]) }}" class="whs-card whs-card--link">
        <div class="whs-card__body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h3 class="mb-1">Maintenance Analytics</h3>
              <p class="text-muted mb-0" style="font-size: 0.875rem;">Track maintenance trends</p>
            </div>
            <i class="icon-base ti ti-arrow-right" style="font-size: 1.5rem; color: var(--whs-brand);"></i>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="{{ route('vehicle-reporting.costs', ['period' => $period]) }}" class="whs-card whs-card--link">
        <div class="whs-card__body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h3 class="mb-1">Cost Analysis</h3>
              <p class="text-muted mb-0" style="font-size: 0.875rem;">Analyze fleet costs</p>
            </div>
            <i class="icon-base ti ti-arrow-right" style="font-size: 1.5rem; color: var(--whs-brand);"></i>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="{{ route('vehicle-reporting.compliance', ['period' => $period]) }}" class="whs-card whs-card--link">
        <div class="whs-card__body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h3 class="mb-1">Compliance Metrics</h3>
              <p class="text-muted mb-0" style="font-size: 0.875rem;">Check compliance status</p>
            </div>
            <i class="icon-base ti ti-arrow-right" style="font-size: 1.5rem; color: var(--whs-brand);"></i>
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Trend Charts -->
  <div class="whs-section-heading">
    <div>
      <h2>Trend analysis</h2>
      <p>{{ ucfirst($period) }}ly maintenance and inspection trends across the fleet.</p>
    </div>
  </div>

  <div class="row g-3">
    <!-- Maintenance Trend Chart -->
    <div class="col-lg-6">
      <div class="whs-card">
        <div class="whs-card__header" style="border-bottom: 1px solid rgba(148, 163, 184, 0.12);">
          <h3>Maintenance Trend</h3>
          <span class="whs-chip whs-chip--status">{{ ucfirst($period) }}ly view</span>
        </div>
        <div class="whs-card__body">
          <div id="maintenanceTrendChart"></div>
        </div>
      </div>
    </div>

    <!-- Inspection Trend Chart -->
    <div class="col-lg-6">
      <div class="whs-card">
        <div class="whs-card__header" style="border-bottom: 1px solid rgba(148, 163, 184, 0.12);">
          <h3>Inspection Trend</h3>
          <span class="whs-chip whs-chip--status">{{ ucfirst($period) }}ly view</span>
        </div>
        <div class="whs-card__body">
          <div id="inspectionTrendChart"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
<script type="module">
window.addEventListener('load', function() {
  // Maintenance Trend Chart
  const maintenanceData = @json($metrics['trends']['maintenance_over_time'] ?? []);
  const maintenanceDates = maintenanceData.map(item => item.date);
  const maintenanceCounts = maintenanceData.map(item => item.count);
  const maintenanceCosts = maintenanceData.map(item => parseFloat(item.cost || 0));

  const maintenanceOptions = {
    series: [
      {
        name: 'Maintenance Count',
        type: 'column',
        data: maintenanceCounts
      },
      {
        name: 'Maintenance Cost ($)',
        type: 'line',
        data: maintenanceCosts
      }
    ],
    chart: {
      type: 'line',
      height: 350,
      toolbar: {
        show: false
      }
    },
    colors: ['#7367f0', '#ff9f43'],
    stroke: {
      width: [0, 4]
    },
    dataLabels: {
      enabled: true,
      enabledOnSeries: [1]
    },
    xaxis: {
      categories: maintenanceDates,
      labels: {
        rotate: -45,
        formatter: function(value) {
          if (!value) return '';
          const date = new Date(value);
          return date.toLocaleDateString('en-AU', { day: '2-digit', month: 'short' });
        }
      }
    },
    yaxis: [
      {
        title: {
          text: 'Maintenance Count'
        }
      },
      {
        opposite: true,
        title: {
          text: 'Cost ($)'
        }
      }
    ],
    legend: {
      position: 'top'
    }
  };

  const maintenanceChart = new ApexCharts(document.querySelector("#maintenanceTrendChart"), maintenanceOptions);
  maintenanceChart.render();

  // Inspection Trend Chart
  const inspectionData = @json($metrics['trends']['inspections_over_time'] ?? []);
  const inspectionDates = inspectionData.map(item => item.date);
  const inspectionCounts = inspectionData.map(item => item.count);

  const inspectionOptions = {
    series: [{
      name: 'Inspections',
      data: inspectionCounts
    }],
    chart: {
      type: 'area',
      height: 350,
      toolbar: {
        show: false
      }
    },
    colors: ['#28c76f'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.7,
        opacityTo: 0.3,
      }
    },
    xaxis: {
      categories: inspectionDates,
      labels: {
        rotate: -45,
        formatter: function(value) {
          if (!value) return '';
          const date = new Date(value);
          return date.toLocaleDateString('en-AU', { day: '2-digit', month: 'short' });
        }
      }
    },
    yaxis: {
      title: {
        text: 'Inspections Count'
      }
    },
    legend: {
      position: 'top'
    }
  };

  const inspectionChart = new ApexCharts(document.querySelector("#inspectionTrendChart"), inspectionOptions);
  inspectionChart.render();
});
</script>
@endsection

