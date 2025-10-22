@extends('layouts.layoutMaster')

@section('title', 'Inspection Analytics')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('content')
{{-- DO NOT CHANGE LAYOUT  WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Inspection Analytics</h4>
  <a href="{{ route('vehicle-reporting.index', ['period' => $period]) }}" class="btn btn-outline-secondary">
    <i class="bx bx-left-arrow-alt me-1"></i> Back to Dashboard
  </a>
</div>

<!-- Inspection Stats - Vuexy CRM Horizontal Avatar Pattern -->
<div class="row g-3 mb-4">
  <!-- Card 1: Due Within 7 Days -->
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
            <h6 class="mb-0">Due (7 Days)</h6>
            <h4 class="mb-0 text-warning">{{ $inspectionMetrics['due_within_7_days'] ?? 0 }}</h4>
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
            <h4 class="mb-0 text-danger">{{ $inspectionMetrics['overdue'] ?? 0 }}</h4>
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
            <h4 class="mb-0">{{ $inspectionMetrics['completed'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 4: Pass Rate -->
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
            <h6 class="mb-0">Pass Rate</h6>
            <h4 class="mb-0 text-success">{{ number_format($inspectionMetrics['pass_rate'] ?? 0, 1) }}%</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Pass/Fail Metrics -->
<div class="row g-3 mb-4">
  <div class="col-md-4 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check-circle fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Passed Inspections</h6>
            <h4 class="mb-0 text-success">{{ $inspectionMetrics['passed'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x-circle fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Failed Inspections</h6>
            <h4 class="mb-0 text-danger">{{ $inspectionMetrics['failed'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-error fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Defects Found</h6>
            <h4 class="mb-0 text-warning">{{ $inspectionMetrics['defects_found'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Inspection Trend Chart -->
<div class="card shadow-none">
  <div class="card-header">
    <h5 class="mb-0">Inspection Trend</h5>
  </div>
  <div class="card-body">
    <div id="inspectionTrendChart"></div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
<script type="module">
window.addEventListener('load', function() {
  const inspectionData = @json($trends['inspections_over_time'] ?? []);
  const inspectionDates = inspectionData.map(item => item.date);
  const inspectionCounts = inspectionData.map(item => item.count);

  const options = {
    series: [{
      name: 'Inspections',
      data: inspectionCounts
    }],
    chart: {
      type: 'line',
      height: 350,
      toolbar: { show: false }
    },
    colors: ['#28c76f'],
    stroke: {
      curve: 'smooth',
      width: 3
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
      title: { text: 'Number of Inspections' }
    }
  };

  const chart = new ApexCharts(document.querySelector("#inspectionTrendChart"), options);
  chart.render();
});
</script>
@endsection
