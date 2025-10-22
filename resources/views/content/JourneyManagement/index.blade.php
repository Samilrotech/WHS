@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Journey Management')

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
  $activeJourneys = $statistics['active_journeys'] ?? 0;
  $overdueJourneys_count = $statistics['overdue_journeys'] ?? 0;
  $emergencyJourneys = $statistics['emergency_journeys'] ?? 0;
  $completedToday = $statistics['completed_today'] ?? 0;

  $filterPills = [
    ['label' => 'All journeys', 'active' => true],
    ['label' => 'Active', 'active' => $activeJourneys > 0],
    ['label' => 'Overdue', 'active' => $overdueJourneys_count > 0],
    ['label' => 'Emergency', 'active' => $emergencyJourneys > 0],
  ];
@endphp

<!-- Overdue Journeys Alert (if any) -->
@if($overdueJourneys->count() > 0)
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
  <h5 class="alert-heading mb-2">
    <i class="icon-base ti ti-alert-octagon me-2"></i>
    {{ $overdueJourneys->count() }} Worker(s) Have Missed Check-Ins
  </h5>
  <p class="mb-3">The following workers have not checked in at their scheduled time:</p>
  <ul class="mb-3">
    @foreach($overdueJourneys as $journey)
    <li>
      <strong>{{ $journey->user->name }}</strong> - {{ $journey->title }}
      (Due: {{ $journey->next_checkin_due->diffForHumans() }})
      <a href="{{ route('journey.show', $journey) }}" class="alert-link">View Journey</a>
    </li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Emergency Journeys Alert (if any) -->
@if($emergencyJourneys > 0)
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
  <h5 class="alert-heading mb-2">
    <i class="icon-base ti ti-alert-octagon me-2"></i>
    {{ $emergencyJourneys }} Emergency Alert(s) Active
  </h5>
  <p class="mb-0">There are active emergency situations. Please check the emergency journeys immediately.</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Journey & Travel"
    title="Journey Management"
    subtitle="Voluntary check-in system with GPS tracking, emergency assistance, and automated overdue alerts for lone worker safety."
    :metric="true"
    metricLabel="Active journeys"
    :metricValue="$statistics['total_journeys'] ?? 0"
    metricCaption="Live journey tracking across WHS4 network"
    :searchRoute="route('journey.index')"
    searchPlaceholder="Search journeys, workers, destinations…"
    :createRoute="route('journey.create')"
    createLabel="Plan journey"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-map"
      iconVariant="brand"
      label="Total Journeys"
      :value="$statistics['total_journeys'] ?? 0"
      meta="All planned and active journeys"
    />

    <x-whs.metric-card
      icon="ti-route"
      iconVariant="warning"
      label="Active"
      :value="$activeJourneys"
      meta="Workers currently traveling"
      metaClass="text-info"
    />

    <x-whs.metric-card
      icon="ti-clock-pause"
      iconVariant="warning"
      label="Overdue"
      :value="$overdueJourneys_count"
      meta="Missed scheduled check-ins"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-alert-octagon"
      iconVariant="critical"
      label="Emergency"
      :value="$emergencyJourneys"
      meta="Active emergency alerts"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Completed Today"
      :value="$completedToday"
      meta="Successfully completed journeys"
      metaClass="text-success"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Journey register</h2>
          <p>Active and planned journeys sorted by start time (newest first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        @forelse ($journeys as $journey)
          @php
            $severity = $journey->status === 'emergency' ? 'critical' : ($journey->checkin_overdue || $journey->status === 'overdue' ? 'high' : 'low');
            $statusLabel = match($journey->status) {
              'planned' => 'Planned',
              'active' => 'Active',
              'completed' => 'Completed',
              'emergency' => 'EMERGENCY',
              'overdue' => 'Overdue',
              default => ucfirst($journey->status)
            };
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $journey->title }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($statusLabel) }}">
                {{ $statusLabel }}
                @if($journey->checkin_overdue && $journey->status === 'active')
                  <i class="icon-base ti ti-clock-pause ms-1"></i>
                @endif
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $journey->user->name }}</h3>
                <p>
                  @if($journey->actual_start_time)
                    Started {{ $journey->actual_start_time->format('d M Y • H:i') }}
                  @else
                    Planned {{ $journey->planned_start_time->format('d M Y • H:i') }}
                  @endif
                </p>
              </div>
              <div>
                <span class="whs-location-label">Destination</span>
                <span>{{ $journey->destination }}</span>
              </div>
              <div>
                <span class="whs-location-label">Vehicle</span>
                <span>
                  @if($journey->vehicle)
                    {{ $journey->vehicle->registration_number }}
                  @else
                    -
                  @endif
                </span>
              </div>
              @if($journey->status === 'active')
              <div>
                <span class="whs-location-label">Next Check-in</span>
                <span @if($journey->checkin_overdue) class="text-warning" @endif>
                  @if($journey->next_checkin_due)
                    @if($journey->checkin_overdue)
                      <i class="icon-base ti ti-clock-pause"></i>
                      {{ $journey->next_checkin_due->diffForHumans() }}
                    @else
                      {{ $journey->next_checkin_due->format('H:i') }}
                    @endif
                  @else
                    -
                  @endif
                </span>
              </div>
              @endif
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('journey.show', $journey) }}" class="whs-action-btn" aria-label="View journey">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                @if($journey->status === 'planned')
                  <a href="{{ route('journey.edit', $journey) }}" class="whs-action-btn" aria-label="Edit journey">
                    <i class="icon-base ti ti-edit"></i>
                    <span>Edit</span>
                  </a>
                  <form action="{{ route('journey.start', $journey) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-player-play"></i>
                      <span>Start</span>
                    </button>
                  </form>
                @endif

                @if($journey->status === 'active')
                  <button type="button" class="whs-action-btn" data-bs-toggle="modal" data-bs-target="#checkinModal{{ $journey->id }}">
                    <i class="icon-base ti ti-map-pin"></i>
                    <span>Check In</span>
                  </button>
                  <button type="button" class="whs-action-btn whs-action-btn--success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $journey->id }}">
                    <i class="icon-base ti ti-circle-check"></i>
                    <span>Complete</span>
                  </button>
                  <button type="button" class="whs-action-btn whs-action-btn--danger" data-bs-toggle="modal" data-bs-target="#emergencyModal{{ $journey->id }}">
                    <i class="icon-base ti ti-alert-triangle"></i>
                    <span>Emergency</span>
                  </button>
                @endif

                @if($journey->status === 'planned')
                  <form action="{{ route('journey.destroy', $journey) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this journey?')">
                      <i class="icon-base ti ti-trash"></i>
                      <span>Delete</span>
                    </button>
                  </form>
                @endif
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>

          {{-- Check-in Modal --}}
          @if($journey->status === 'active')
          <div class="modal fade" id="checkinModal{{ $journey->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form action="{{ route('journey.checkin', $journey) }}" method="POST">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Check In - {{ $journey->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Status *</label>
                      <select name="status" class="form-select" required>
                        <option value="ok">OK - All good</option>
                        <option value="assistance_needed">Assistance Needed</option>
                        <option value="emergency">EMERGENCY</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Notes (Optional)</label>
                      <textarea name="notes" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
                    </div>
                    <input type="hidden" name="latitude" id="checkin_latitude_{{ $journey->id }}">
                    <input type="hidden" name="longitude" id="checkin_longitude_{{ $journey->id }}">
                    <small class="text-muted">GPS coordinates will be captured automatically.</small>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Check-In</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          {{-- Complete Modal --}}
          <div class="modal fade" id="completeModal{{ $journey->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form action="{{ route('journey.complete', $journey) }}" method="POST">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Complete Journey - {{ $journey->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Completion Notes *</label>
                      <textarea name="completion_notes" class="form-control" rows="4" required placeholder="Journey completed successfully. All objectives met."></textarea>
                    </div>
                    <small class="text-muted">Please confirm you have arrived safely and all tasks are completed.</small>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Journey</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          {{-- Emergency Modal --}}
          <div class="modal fade" id="emergencyModal{{ $journey->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form action="{{ route('journey.emergency', $journey) }}" method="POST">
                @csrf
                <div class="modal-content border-danger">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                      <i class="icon-base ti ti-alert-octagon me-2"></i>
                      EMERGENCY ALERT
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                      <strong>WARNING:</strong> This will trigger an emergency alert and notify:
                      <ul class="mb-0 mt-2">
                        <li>Emergency Contact: {{ $journey->emergency_contact_name }} ({{ $journey->emergency_contact_phone }})</li>
                        <li>Branch Supervisor</li>
                        <li>All nearby workers</li>
                      </ul>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Emergency Details *</label>
                      <textarea name="notes" class="form-control" rows="4" required placeholder="Describe the emergency situation..."></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                      <i class="icon-base ti ti-alert-octagon me-1"></i>
                      TRIGGER EMERGENCY
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          @endif

        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-map whs-empty__icon"></i>
              <h3>No journeys yet</h3>
              <p>No journeys have been planned. Start tracking worker travel with voluntary check-ins.</p>
              <a href="{{ route('journey.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Plan first journey
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Journey status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active</span>
            <strong class="text-info">{{ $activeJourneys }}</strong>
          </li>
          <li>
            <span>Overdue Check-ins</span>
            <strong class="text-warning">{{ $overdueJourneys_count }}</strong>
          </li>
          <li>
            <span>Emergency</span>
            <strong class="text-danger">{{ $emergencyJourneys }}</strong>
          </li>
          <li>
            <span>Completed Today</span>
            <strong class="text-success">{{ $completedToday }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Journey management provides non-intrusive safety tracking with automated overdue alerts.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Voluntary check-in system">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Plan Journey</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Set destination and check-in intervals</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Start Journey</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">GPS location captured automatically</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Voluntary Check-ins</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Worker-initiated status updates</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Overdue Alerts</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated notifications when missed</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Complete Safely</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Confirm arrival and close journey</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

{{-- Hidden table for DataTables compatibility - will be replaced in next iteration --}}
<div style="display: none;">
  <table id="journeysTable" class="table table-hover">
      <thead>
        <tr>
          <th>Title</th>
          <th>Worker</th>
          <th>Vehicle</th>
          <th>Destination</th>
          <th>Start Time</th>
          <th>Status</th>
          <th>Next Check-in</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($journeys as $journey)
        <tr class="{{ $journey->checkin_overdue ? 'table-warning' : '' }} {{ $journey->status === 'emergency' ? 'table-danger' : '' }}">
          <td>
            <strong>{{ $journey->title }}</strong>
            @if($journey->checkin_overdue)
            <i class="bx bx-time text-warning ms-1" title="Check-in overdue"></i>
            @endif
          </td>
          <td>{{ $journey->user->name }}</td>
          <td>
            @if($journey->vehicle)
            <span class="badge bg-label-secondary">{{ $journey->vehicle->registration_number }}</span>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td>{{ $journey->destination }}</td>
          <td>
            @if($journey->actual_start_time)
            {{ $journey->actual_start_time->format('d/m/Y H:i') }}
            @else
            <span class="text-muted">{{ $journey->planned_start_time->format('d/m/Y H:i') }}</span>
            @endif
          </td>
          <td>
            @switch($journey->status)
              @case('planned')
                <span class="badge bg-secondary">Planned</span>
                @break
              @case('active')
                <span class="badge bg-info">Active</span>
                @break
              @case('completed')
                <span class="badge bg-success">Completed</span>
                @break
              @case('emergency')
                <span class="badge bg-danger">
                  <i class="bx bx-error-circle me-1"></i>EMERGENCY
                </span>
                @break
              @case('overdue')
                <span class="badge bg-warning">Overdue</span>
                @break
            @endswitch
          </td>
          <td>
            @if($journey->status === 'active')
              @if($journey->next_checkin_due)
                @if($journey->checkin_overdue)
                  <span class="text-warning">
                    <i class="bx bx-time"></i>
                    {{ $journey->next_checkin_due->diffForHumans() }}
                  </span>
                @else
                  <span class="text-muted">{{ $journey->next_checkin_due->format('H:i') }}</span>
                @endif
              @else
                <span class="text-muted">-</span>
              @endif
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="icon-base ti ti-dots-vertical icon-20px"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('journey.show', $journey) }}">
                  <i class="icon-base ti ti-eye me-1"></i> View
                </a>

                @if($journey->status === 'planned')
                  <a class="dropdown-item" href="{{ route('journey.edit', $journey) }}">
                    <i class="icon-base ti ti-edit me-1"></i> Edit
                  </a>
                  <form action="{{ route('journey.start', $journey) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item text-info">
                      <i class="icon-base ti ti-player-play me-1"></i> Start Journey
                    </button>
                  </form>
                @endif

                @if($journey->status === 'active')
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#checkinModal{{ $journey->id }}">
                    <i class="icon-base ti ti-map-pin me-1"></i> Check In
                  </a>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#completeModal{{ $journey->id }}">
                    <i class="icon-base ti ti-circle-check me-1"></i> Complete
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#emergencyModal{{ $journey->id }}">
                    <i class="icon-base ti ti-alert-triangle me-1"></i> Emergency
                  </a>
                @endif

                @if($journey->status === 'planned')
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('journey.destroy', $journey) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">
                      <i class="icon-base ti ti-trash me-1"></i> Delete
                    </button>
                  </form>
                @endif
              </div>
            </div>
          </td>
        </tr>

        {{-- Check-in Modal --}}
        @if($journey->status === 'active')
        <div class="modal fade" id="checkinModal{{ $journey->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form action="{{ route('journey.checkin', $journey) }}" method="POST">
              @csrf
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Check In - {{ $journey->title }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                      <option value="ok">OK - All good</option>
                      <option value="assistance_needed">Assistance Needed</option>
                      <option value="emergency">EMERGENCY</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
                  </div>
                  <input type="hidden" name="latitude" id="checkin_latitude_{{ $journey->id }}">
                  <input type="hidden" name="longitude" id="checkin_longitude_{{ $journey->id }}">
                  <small class="text-muted">GPS coordinates will be captured automatically.</small>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Submit Check-In</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Complete Modal --}}
        <div class="modal fade" id="completeModal{{ $journey->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form action="{{ route('journey.complete', $journey) }}" method="POST">
              @csrf
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Complete Journey - {{ $journey->title }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Completion Notes *</label>
                    <textarea name="completion_notes" class="form-control" rows="4" required placeholder="Journey completed successfully. All objectives met."></textarea>
                  </div>
                  <small class="text-muted">Please confirm you have arrived safely and all tasks are completed.</small>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-success">Complete Journey</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Emergency Modal --}}
        <div class="modal fade" id="emergencyModal{{ $journey->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form action="{{ route('journey.emergency', $journey) }}" method="POST">
              @csrf
              <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title">
                    <i class="bx bx-error-circle me-2"></i>
                    EMERGENCY ALERT
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="alert alert-danger mb-3">
                    <strong>WARNING:</strong> This will trigger an emergency alert and notify:
                    <ul class="mb-0 mt-2">
                      <li>Emergency Contact: {{ $journey->emergency_contact_name }} ({{ $journey->emergency_contact_phone }})</li>
                      <li>Branch Supervisor</li>
                      <li>All nearby workers</li>
                    </ul>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Emergency Details *</label>
                    <textarea name="notes" class="form-control" rows="4" required placeholder="Describe the emergency situation..."></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-danger">
                    <i class="bx bx-error-circle me-1"></i>
                    TRIGGER EMERGENCY
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        @endif

        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')
@endsection

@section('page-script')
<script type="module">
// Wait for window load to ensure all Vite modules (jQuery, DataTables) are ready
window.addEventListener('load', function() {
  var dt = $('#journeysTable').DataTable({
    order: [[4, 'desc']], // Sort by start time
    pageLength: 25,
    responsive: {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function (row) {
            var data = row.data();
            return 'Details for Journey: ' + data[0];
          }
        }),
        renderer: $.fn.dataTable.Responsive.renderer.tableAll()
      }
    },
    columnDefs: [
      { responsivePriority: 1, targets: 0 },   // Title - always visible
      { responsivePriority: 2, targets: 5 },   // Status - always visible
      { responsivePriority: 3, targets: 6 },   // Next Check-in - always visible
      { responsivePriority: 4, targets: -1 },  // Actions - always visible
      { responsivePriority: 10, targets: '_all' }
    ],
    language: {
      search: "",
      searchPlaceholder: "Search journeys..."
    },
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-end"f>>' +
         '<"row"<"col-sm-12"<"card-datatable table-responsive"t>>>' +
         '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });

  // Add "Plan Journey" button to the table
  $('#journeysTable').closest('.card-body').prepend(
    '<div class="d-flex justify-content-between align-items-center mb-3">' +
      '<div></div>' +
      '<a href="{{ route("journey.create") }}" class="btn btn-primary">' +
        '<i class="icon-base ti ti-plus me-1"></i> Plan Journey' +
      '</a>' +
    '</div>'
  );

  // GPS Capture for Check-ins
  function captureGPS(journeyId) {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function(position) {
          document.getElementById('checkin_latitude_' + journeyId).value = position.coords.latitude;
          document.getElementById('checkin_longitude_' + journeyId).value = position.coords.longitude;
        },
        function(error) {
          console.log('GPS capture failed:', error);
        }
      );
    }
  }

  // Capture GPS when check-in modals open
  @foreach($journeys as $journey)
    @if($journey->status === 'active')
    $('#checkinModal{{ $journey->id }}').on('show.bs.modal', function() {
      captureGPS('{{ $journey->id }}');
    });
    @endif
  @endforeach

  // Real-time updates via Laravel Echo (if configured)
  @if(auth()->check())
  if (typeof window.Echo !== 'undefined') {
    window.Echo.private('journeys.{{ auth()->user()->branch_id }}')
      .listen('JourneyStarted', (e) => {
        if (typeof toastr !== 'undefined') {
          toastr.info('Journey started: ' + e.journey.title);
        }
        location.reload();
      })
      .listen('JourneyEmergency', (e) => {
        if (typeof toastr !== 'undefined') {
          toastr.error('EMERGENCY: ' + e.journey.title);
        }
        location.reload();
      })
      .listen('CheckinOverdue', (e) => {
        if (typeof toastr !== 'undefined') {
          toastr.warning('Check-in overdue: ' + e.journey.user.name);
        }
        location.reload();
      });
  }
  @endif
});
</script>
@endsection

