@extends('layouts.layoutMaster')

@section('title', 'Risk Matrix 5×5')

@section('content')
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Risk Matrix 5×5</h4>
  <div>
    <a href="{{ route('risk.index') }}" class="btn btn-outline-secondary me-2">
      <i class='bx bx-list-ul me-1'></i> Risk List
    </a>
    <a href="{{ route('risk.create') }}" class="btn btn-primary">
      <i class='bx bx-plus me-1'></i> Add Risk Assessment
    </a>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="badge p-2 bg-label-primary mb-0 rounded me-3">
            <i class="icon-base bx bx-shield icon-28px"></i>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Total Risks</h6>
            <h4 class="mb-0">{{ $statistics['total'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="badge p-2 bg-label-danger mb-0 rounded me-3">
            <i class="icon-base bx bx-error icon-28px"></i>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Critical (20-25)</h6>
            <h4 class="mb-0 text-danger">{{ $statistics['critical'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="badge p-2 bg-label-warning mb-0 rounded me-3">
            <i class="icon-base bx bx-error-circle icon-28px"></i>
          </div>
          <div class="card-info">
            <h6 class="mb-0">High (12-19)</h6>
            <h4 class="mb-0 text-warning">{{ $statistics['high'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="badge p-2 bg-label-success mb-0 rounded me-3">
            <i class="icon-base bx bx-check-shield icon-28px"></i>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Low (1-5)</h6>
            <h4 class="mb-0 text-success">{{ $statistics['low'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Risk Matrix 5x5 -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Risk Matrix: Likelihood × Consequence</h5>
    <p class="text-muted mb-0 small">Click on any cell to view risks at that level</p>
  </div>
  <div class="card-body">
    <!-- Matrix Legend -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex flex-wrap gap-3 justify-content-center">
          <div class="d-flex align-items-center">
            <span class="badge bg-success me-2" style="width: 20px; height: 20px;"></span>
            <small>Low Risk (1-5)</small>
          </div>
          <div class="d-flex align-items-center">
            <span class="badge bg-warning me-2" style="width: 20px; height: 20px;"></span>
            <small>Medium Risk (6-11)</small>
          </div>
          <div class="d-flex align-items-center">
            <span class="badge me-2" style="width: 20px; height: 20px; background-color: #ff9f43;"></span>
            <small>High Risk (12-19)</small>
          </div>
          <div class="d-flex align-items-center">
            <span class="badge bg-danger me-2" style="width: 20px; height: 20px;"></span>
            <small>Critical Risk (20-25)</small>
          </div>
        </div>
      </div>
    </div>

    <!-- 5x5 Matrix Table -->
    <div class="table-responsive">
      <table class="table table-bordered text-center risk-matrix-table">
        <thead>
          <tr>
            <th class="bg-light" style="width: 100px;">
              <div class="fw-bold">Likelihood</div>
              <div class="fw-bold">↓</div>
            </th>
            <th class="bg-light">
              <div class="fw-bold">Insignificant</div>
              <div class="small text-muted">(1)</div>
            </th>
            <th class="bg-light">
              <div class="fw-bold">Minor</div>
              <div class="small text-muted">(2)</div>
            </th>
            <th class="bg-light">
              <div class="fw-bold">Moderate</div>
              <div class="small text-muted">(3)</div>
            </th>
            <th class="bg-light">
              <div class="fw-bold">Major</div>
              <div class="small text-muted">(4)</div>
            </th>
            <th class="bg-light">
              <div class="fw-bold">Catastrophic</div>
              <div class="small text-muted">(5)</div>
            </th>
          </tr>
          <tr>
            <th class="bg-light" colspan="6">
              <div class="fw-bold">Consequence →</div>
            </th>
          </tr>
        </thead>
        <tbody>
          @for($likelihood = 5; $likelihood >= 1; $likelihood--)
          <tr>
            <th class="bg-light">
              <div class="fw-bold">
                @if($likelihood == 5) Almost Certain
                @elseif($likelihood == 4) Likely
                @elseif($likelihood == 3) Possible
                @elseif($likelihood == 2) Unlikely
                @else Rare
                @endif
              </div>
              <div class="small text-muted">({{ $likelihood }})</div>
            </th>
            @for($consequence = 1; $consequence <= 5; $consequence++)
              @php
                $score = $likelihood * $consequence;
                $level = match(true) {
                  $score <= 5 => 'green',
                  $score <= 11 => 'yellow',
                  $score <= 19 => 'orange',
                  default => 'red'
                };

                // Bootstrap color classes
                $bgClass = match($level) {
                  'green' => 'bg-success',
                  'yellow' => 'bg-warning',
                  'orange' => '',
                  'red' => 'bg-danger'
                };

                $textClass = ($level === 'yellow' || $level === 'orange') ? 'text-dark' : 'text-white';

                // Custom orange style
                $customStyle = $level === 'orange' ? 'background-color: #ff9f43; color: white;' : '';

                // Count risks at this level
                $cellRisks = collect($matrixData)->filter(function($cell) use ($likelihood, $consequence) {
                  return $cell['likelihood'] == $likelihood && $cell['consequence'] == $consequence;
                })->first();

                $count = $cellRisks['count'] ?? 0;
              @endphp
              <td class="{{ $bgClass }} {{ $textClass }} position-relative"
                  style="height: 100px; {{ $customStyle }}"
                  role="button"
                  @if($count > 0)
                  onclick="window.location.href='{{ route('risk.index', ['likelihood' => $likelihood, 'consequence' => $consequence]) }}'"
                  @endif>
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                  <div class="fw-bold fs-4">{{ $score }}</div>
                  @if($count > 0)
                    <div class="badge bg-white text-dark mt-2">
                      {{ $count }} {{ $count == 1 ? 'Risk' : 'Risks' }}
                    </div>
                  @else
                    <div class="small opacity-75 mt-2">No risks</div>
                  @endif
                </div>
              </td>
            @endfor
          </tr>
          @endfor
        </tbody>
      </table>
    </div>

    <!-- Matrix Explanation -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="alert alert-info mb-0">
          <h6 class="alert-heading">
            <i class='bx bx-info-circle me-1'></i> How to Use This Matrix
          </h6>
          <ul class="mb-0 small">
            <li><strong>Likelihood (Vertical):</strong> How likely is the hazard to occur? (1=Rare, 5=Almost Certain)</li>
            <li><strong>Consequence (Horizontal):</strong> How severe would the impact be? (1=Insignificant, 5=Catastrophic)</li>
            <li><strong>Risk Score:</strong> Likelihood × Consequence (1-25)</li>
            <li><strong>Risk Levels:</strong> Green (1-5 Low), Yellow (6-11 Medium), Orange (12-19 High), Red (20-25 Critical)</li>
            <li><strong>Action:</strong> Click on any cell with risks to view and manage them</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-style')
<style>
.risk-matrix-table {
  font-size: 0.875rem;
}

.risk-matrix-table th,
.risk-matrix-table td {
  vertical-align: middle;
  padding: 0.75rem;
}

.risk-matrix-table td[role="button"] {
  cursor: pointer;
  transition: opacity 0.2s;
}

.risk-matrix-table td[role="button"]:hover {
  opacity: 0.85;
  transform: scale(1.02);
}

.icon-base {
  font-size: 1.75rem;
}

.icon-28px {
  font-size: 28px;
}
</style>
@endsection
