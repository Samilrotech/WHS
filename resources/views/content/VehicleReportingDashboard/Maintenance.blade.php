@extends('layouts.layoutMaster')

@section('title', 'Maintenance Analytics')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('content')
{{-- DO NOT CHANGE LAYOUT  WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Maintenance Analytics</h4>
  <a href="{{ route('vehicle-reporting.index', ['period' => $period]) }}" class="btn btn-outline-secondary">
    <i class="bx bx-left-arrow-alt me-1"></i> Back to Dashboard
  </a>
</div>

<!-- Maintenance Stats - Vuexy CRM Horizontal Avatar Pattern -->
<div class="row g-3 mb-4">
  <!-- Card 1: Due Within 14 Days -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-calendar-exclamation fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Due (14 Days)</h6>
            <h4 class="mb-0 text-warning">{{ $maintenanceMetrics['due_within_14_days'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 2: Overdue -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-calendar-x fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Overdue</h6>
            <h4 class="mb-0 text-danger">{{ $maintenanceMetrics['overdue'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 3: Completed -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-check-double fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Completed</h6>
            <h4 class="mb-0">{{ $maintenanceMetrics['completed'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 4: Preventive Ratio -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-trending-up fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Preventive Ratio</h6>
            <h4 class="mb-0 text-success">{{ number_format($maintenanceMetrics['preventive_ratio'] ?? 0, 1) }}%</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Preventive vs Corrective Breakdown -->
<div class="row g-3 mb-4">
  <div class="col-md-6 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-shield-quarter fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Preventive Maintenance</h6>
            <h4 class="mb-0 text-success">{{ $maintenanceMetrics['preventive'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-wrench fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Corrective Maintenance</h6>
            <h4 class="mb-0 text-warning">{{ $maintenanceMetrics['corrective'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Maintenance Trend Chart -->
<div class="card shadow-none">
  <div class="card-header">
    <h5 class="mb-0">Maintenance Trend</h5>
  </div>
  <div class="card-body">
    <div id="maintenanceTrendChart"></div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
<script type="module">
window.addEventListener('load', function() {
  const maintenanceData = @json($trends['maintenance_over_time'] ?? []);
  const maintenanceDates = maintenanceData.map(item => item.date);
  const maintenanceCounts = maintenanceData.map(item => item.count);
  const maintenanceCosts = maintenanceData.map(item => parseFloat(item.cost || 0));

  const options = {
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
      toolbar: { show: false }
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
        title: { text: 'Maintenance Count' }
      },
      {
        opposite: true,
        title: { text: 'Cost ($)' }
      }
    ],
    legend: {
      position: 'top'
    }
  };

  const chart = new ApexCharts(document.querySelector("#maintenanceTrendChart"), options);
  chart.render();
});
</script>
@endsection
