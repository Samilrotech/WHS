@extends('layouts.layoutMaster')

@section('title', 'Risk Assessment Details')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
@endsection

@section('content')
{{-- DO NOT CHANGE LAYOUT — WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="row">
  <!-- Risk Details Card -->
  <div class="col-lg-8 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Risk Assessment Details</h5>
        <div class="d-flex gap-2">
          <a href="{{ route('risk.edit', $assessment) }}" class="btn btn-sm btn-warning">
            <i class='bx bx-edit me-1'></i> Edit
          </a>
          <a href="{{ route('risk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class='bx bx-arrow-back me-1'></i> Back to List
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Task Description & Risk Level -->
        <div class="mb-4">
          <h4 class="mb-2">{{ $assessment->task_description }}</h4>
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge bg-label-secondary">{{ ucwords(str_replace('-', ' ', $assessment->category)) }}</span>
            @if($assessment->initial_risk_score >= 20)
              <span class="badge bg-danger">Critical Risk ({{ $assessment->initial_risk_score }})</span>
            @elseif($assessment->initial_risk_score >= 12)
              <span class="badge bg-warning">High Risk ({{ $assessment->initial_risk_score }})</span>
            @elseif($assessment->initial_risk_score >= 6)
              <span class="badge bg-warning">Medium Risk ({{ $assessment->initial_risk_score }})</span>
            @else
              <span class="badge bg-success">Low Risk ({{ $assessment->initial_risk_score }})</span>
            @endif
            <span class="badge bg-label-info">{{ ucfirst($assessment->status) }}</span>
          </div>
        </div>

        <!-- Basic Information -->
        <div class="mb-4">
          <h6 class="mb-3">Basic Information</h6>
          <div class="row">
            <div class="col-md-6 mb-2">
              <small class="text-muted d-block">Location</small>
              <strong>{{ $assessment->location }}</strong>
            </div>
            <div class="col-md-6 mb-2">
              <small class="text-muted d-block">Assessment Date</small>
              <strong>{{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d/m/Y') }}</strong>
            </div>
            @if($assessment->review_date)
            <div class="col-md-6 mb-2">
              <small class="text-muted d-block">Review Date</small>
              <strong>{{ \Carbon\Carbon::parse($assessment->review_date)->format('d/m/Y') }}</strong>
            </div>
            @endif
          </div>
        </div>

        <hr>

        <!-- Initial Risk (Before Controls) -->
        <div class="mb-4">
          <h6 class="mb-3">Initial Risk (Before Controls)</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class='bx bx-pulse'></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Likelihood</small>
                  <strong>{{ $assessment->initial_likelihood }}/5</strong>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class='bx bx-error-alt'></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Consequence</small>
                  <strong>{{ $assessment->initial_consequence }}/5</strong>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded bg-label-danger">
                    <i class='bx bx-shield-x'></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Initial Risk Score</small>
                  <strong>{{ $assessment->initial_risk_score }}/25</strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <hr>

        <!-- Residual Risk (After Controls) -->
        <div class="mb-4">
          <h6 class="mb-3">Residual Risk (After Controls)</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class='bx bx-pulse'></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Likelihood</small>
                  <strong>{{ $assessment->residual_likelihood }}/5</strong>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class='bx bx-error-alt'></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Consequence</small>
                  <strong>{{ $assessment->residual_consequence }}/5</strong>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded
                    @if($assessment->residual_risk_score >= 20) bg-label-danger
                    @elseif($assessment->residual_risk_score >= 12) bg-label-warning
                    @elseif($assessment->residual_risk_score >= 6) bg-label-warning
                    @else bg-label-success
                    @endif">
                    <i class='bx bx-shield-check'></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Residual Risk Score</small>
                  <strong>{{ $assessment->residual_risk_score }}/25</strong>
                </div>
              </div>
            </div>
          </div>
          <div class="alert
            @if($assessment->residual_risk_score >= 20) alert-danger
            @elseif($assessment->residual_risk_score >= 12) alert-warning
            @elseif($assessment->residual_risk_score >= 6) alert-warning
            @else alert-success
            @endif mt-3 mb-0">
            <strong>Risk Reduction:</strong> Controls reduced risk from {{ $assessment->initial_risk_score }} to {{ $assessment->residual_risk_score }}
            ({{ $assessment->initial_risk_score - $assessment->residual_risk_score }} point reduction)
          </div>
        </div>

        <!-- Metadata -->
        <div class="border-top pt-3">
          <div class="row">
            <div class="col-md-6">
              <small class="text-muted d-block mb-1">
                <i class='bx bx-user me-1'></i> Created by: <strong>{{ $assessment->user->name ?? 'Unknown' }}</strong>
              </small>
              <small class="text-muted d-block mb-1">
                <i class='bx bx-calendar me-1'></i> Created: <strong>{{ $assessment->created_at->format('d/m/Y H:i') }}</strong>
              </small>
            </div>
            <div class="col-md-6">
              <small class="text-muted d-block mb-1">
                <i class='bx bx-time me-1'></i> Last updated: <strong>{{ $assessment->updated_at->format('d/m/Y H:i') }}</strong>
              </small>
              <small class="text-muted d-block">
                <i class='bx bx-building me-1'></i> Branch: <strong>{{ $assessment->branch->name ?? 'Unknown' }}</strong>
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Risk Matrix Card -->
  <div class="col-lg-4 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Risk Matrix Position</h5>
      </div>
      <div class="card-body">
        <div id="riskMatrixChart"></div>
        <div class="mt-3">
          <small class="text-muted d-block mb-2"><strong>Risk Level Legend:</strong></small>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-success">Low (1-5)</span>
            <span class="badge bg-warning">Medium (6-11)</span>
            <span class="badge bg-warning">High (12-19)</span>
            <span class="badge bg-danger">Critical (20-25)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Additional Information Card -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Likelihood & Consequence Definitions</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Likelihood Definitions -->
          <div class="col-md-6 mb-3">
            <h6 class="text-primary mb-3">Likelihood Scale</h6>
            <ul class="list-unstyled">
              <li class="mb-2">
                <span class="badge bg-label-primary me-2">1</span>
                <strong>Rare:</strong> May occur only in exceptional circumstances
              </li>
              <li class="mb-2">
                <span class="badge bg-label-primary me-2">2</span>
                <strong>Unlikely:</strong> Could occur at some time
              </li>
              <li class="mb-2">
                <span class="badge bg-label-primary me-2">3</span>
                <strong>Possible:</strong> Might occur at some time
              </li>
              <li class="mb-2">
                <span class="badge bg-label-primary me-2">4</span>
                <strong>Likely:</strong> Will probably occur in most circumstances
              </li>
              <li class="mb-2">
                <span class="badge bg-label-primary me-2">5</span>
                <strong>Almost Certain:</strong> Expected to occur in most circumstances
              </li>
            </ul>
          </div>

          <!-- Consequence Definitions -->
          <div class="col-md-6 mb-3">
            <h6 class="text-danger mb-3">Consequence Scale</h6>
            <ul class="list-unstyled">
              <li class="mb-2">
                <span class="badge bg-label-danger me-2">1</span>
                <strong>Insignificant:</strong> No injuries, minimal impact
              </li>
              <li class="mb-2">
                <span class="badge bg-label-danger me-2">2</span>
                <strong>Minor:</strong> First aid treatment, minor delays
              </li>
              <li class="mb-2">
                <span class="badge bg-label-danger me-2">3</span>
                <strong>Moderate:</strong> Medical treatment required, temporary disruption
              </li>
              <li class="mb-2">
                <span class="badge bg-label-danger me-2">4</span>
                <strong>Major:</strong> Serious injuries, significant business interruption
              </li>
              <li class="mb-2">
                <span class="badge bg-label-danger me-2">5</span>
                <strong>Catastrophic:</strong> Death, permanent disability, major financial loss
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script type="module">
// Wait for window load to ensure all Vite modules are ready
window.addEventListener('load', function() {
  // Risk Matrix Heatmap
  var riskMatrix = [
    {x: 'Rare', y: 5, value: 5},
    {x: 'Unlikely', y: 5, value: 10},
    {x: 'Possible', y: 5, value: 15},
    {x: 'Likely', y: 5, value: 20},
    {x: 'Almost Certain', y: 5, value: 25},

    {x: 'Rare', y: 4, value: 4},
    {x: 'Unlikely', y: 4, value: 8},
    {x: 'Possible', y: 4, value: 12},
    {x: 'Likely', y: 4, value: 16},
    {x: 'Almost Certain', y: 4, value: 20},

    {x: 'Rare', y: 3, value: 3},
    {x: 'Unlikely', y: 3, value: 6},
    {x: 'Possible', y: 3, value: 9},
    {x: 'Likely', y: 3, value: 12},
    {x: 'Almost Certain', y: 3, value: 15},

    {x: 'Rare', y: 2, value: 2},
    {x: 'Unlikely', y: 2, value: 4},
    {x: 'Possible', y: 2, value: 6},
    {x: 'Likely', y: 2, value: 8},
    {x: 'Almost Certain', y: 2, value: 10},

    {x: 'Rare', y: 1, value: 1},
    {x: 'Unlikely', y: 1, value: 2},
    {x: 'Possible', y: 1, value: 3},
    {x: 'Likely', y: 1, value: 4},
    {x: 'Almost Certain', y: 1, value: 5}
  ];

  // Current risk position
  var currentLikelihood = {{ $assessment->initial_likelihood }};
  var currentConsequence = {{ $assessment->initial_consequence }};
  var likelihoodLabels = ['Rare', 'Unlikely', 'Possible', 'Likely', 'Almost Certain'];

  var options = {
    series: [{
      name: 'Risk Score',
      data: riskMatrix
    }],
    chart: {
      height: 350,
      type: 'heatmap',
      toolbar: {
        show: false
      }
    },
    plotOptions: {
      heatmap: {
        colorScale: {
          ranges: [
            {from: 1, to: 5, color: '#28c76f', name: 'Low'},
            {from: 6, to: 11, color: '#ff9f43', name: 'Medium'},
            {from: 12, to: 19, color: '#ff9f43', name: 'High'},
            {from: 20, to: 25, color: '#ea5455', name: 'Critical'}
          ]
        }
      }
    },
    dataLabels: {
      enabled: true,
      style: {
        colors: ['#fff']
      }
    },
    xaxis: {
      type: 'category',
      title: {
        text: 'Likelihood'
      },
      categories: ['Rare', 'Unlikely', 'Possible', 'Likely', 'Almost Certain']
    },
    yaxis: {
      title: {
        text: 'Consequence'
      },
      labels: {
        formatter: function(val) {
          var labels = ['', 'Insignificant', 'Minor', 'Moderate', 'Major', 'Catastrophic'];
          return labels[val];
        }
      }
    },
    title: {
      text: 'Initial Risk: ' + likelihoodLabels[currentLikelihood - 1] + ' × Consequence ' + currentConsequence,
      style: {
        fontSize: '12px',
        fontWeight: 'bold'
      }
    },
    tooltip: {
      y: {
        formatter: function(val) {
          return 'Risk Score: ' + val;
        }
      }
    },
    annotations: {
      points: [{
        x: likelihoodLabels[currentLikelihood - 1],
        y: currentConsequence,
        marker: {
          size: 10,
          fillColor: '#fff',
          strokeColor: '#000',
          strokeWidth: 3,
          shape: 'circle'
        },
        label: {
          borderColor: '#000',
          offsetY: 0,
          style: {
            color: '#fff',
            background: '#000'
          },
          text: 'Current'
        }
      }]
    }
  };

  var chart = new ApexCharts(document.querySelector("#riskMatrixChart"), options);
  chart.render();
});
</script>
@endsection
