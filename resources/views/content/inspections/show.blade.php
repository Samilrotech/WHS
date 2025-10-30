@extends('layouts.layoutMaster')

@section('title', 'Inspection Details')

@section('content')
@php
  use Illuminate\Support\Str;
  $vehicle = $inspection->vehicle;
@endphp
@include('layouts.sections.flash-message')

<div class="row inspection-details-page">
  <div class="col-12 col-lg-8">
    <!-- Inspection Header -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">{{ $inspection->inspection_number }}</h5>
          <p class="mb-0 text-muted">
            @if($vehicle)
              {{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}
            @else
              Vehicle record unavailable
            @endif
          </p>
        </div>

        <a href="{{ route('inspections.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i> Back
        </a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <small class="text-muted">Status</small>
            <p class="fw-semibold">
              @switch($inspection->status)
                @case('pending') <span class="badge bg-secondary">Pending</span> @break
                @case('in_progress') <span class="badge bg-info">In Progress</span> @break
                @case('completed') <span class="badge bg-warning">Completed</span> @break
                @case('approved') <span class="badge bg-success">Approved</span> @break
                @case('rejected') <span class="badge bg-danger">Rejected</span> @break
              @endswitch
            </p>
          </div>
          <div class="col-md-4">
            <small class="text-muted">Overall Result</small>
            <p class="fw-semibold">
              @if($inspection->overall_result)
                @switch($inspection->overall_result)
                  @case('pass') <span class="badge bg-success">Pass</span> @break
                  @case('pass_minor') <span class="badge bg-warning">Pass (Minor)</span> @break
                  @case('fail_major') <span class="badge bg-danger">Fail (Major)</span> @break
                  @case('fail_critical') <span class="badge bg-danger">Fail (Critical)</span> @break
                @endswitch
              @else
                <span class="text-muted">Not completed</span>
              @endif
            </p>
          </div>
          <div class="col-md-4">
            <small class="text-muted">Completion</small>
            <div class="d-flex align-items-center">
              <div class="progress flex-grow-1 me-2" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $statistics['completion_percentage'] }}%"></div>
              </div>
              <small>{{ $statistics['completion_percentage'] }}%</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Inspection Checklist -->
    @foreach($itemsByCategory as $category => $items)
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">{{ $category }}</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive d-none d-md-block">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th scope="col">Item</th>
                <th scope="col" class="text-nowrap">Result</th>
                <th scope="col" class="text-nowrap">Severity</th>
                <th scope="col" class="text-nowrap d-none d-lg-table-cell">Details</th>
                <th scope="col" class="text-nowrap d-none d-lg-table-cell">Notes</th>
                <th scope="col" class="text-nowrap">Photos</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $item)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    {{ $item->item_name }}
                    @if($item->safety_critical)
                      <span class="badge bg-label-danger ms-2" title="Safety Critical">!</span>
                    @endif
                    @if($item->compliance_item)
                      <span class="badge bg-label-warning ms-1" title="Compliance">C</span>
                    @endif
                  </div>
                  @if($item->item_description)
                    <small class="text-muted">{{ $item->item_description }}</small>
                  @endif
                </td>
                <td>
                  @switch($item->result)
                    @case('pass') <span class="badge bg-success">Pass</span> @break
                    @case('fail') <span class="badge bg-danger">Fail</span> @break
                    @case('na') <span class="badge bg-secondary">N/A</span> @break
                    @default <span class="badge bg-secondary">Pending</span>
                  @endswitch
                </td>
                <td>
                  @if($item->defect_severity && $item->defect_severity !== 'none')
                    @switch($item->defect_severity)
                      @case('critical') <span class="badge bg-danger">Critical</span> @break
                      @case('major') <span class="badge bg-warning">Major</span> @break
                      @case('minor') <span class="badge bg-info">Minor</span> @break
                    @endswitch
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="d-none d-lg-table-cell">
                  @if($item->measurement_value)
                    <span>{{ $item->measurement_value }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="d-none d-lg-table-cell">
                  @if($item->defect_notes)
                    <small>{{ Str::limit($item->defect_notes, 50) }}</small>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @php
                    $photos = $item->photo_gallery ?? [];
                  @endphp
                  @if(!empty($photos))
                    <div class="d-flex flex-wrap gap-2">
                      @foreach($photos as $photo)
                        <button
                          type="button"
                          class="inspection-photo-trigger"
                          data-bs-toggle="modal"
                          data-bs-target="#inspectionPhotoModal"
                          data-photo-src="{{ $photo['url'] }}"
                          data-photo-label="{{ $photo['label'] }}"
                          data-photo-download="{{ $photo['download_url'] ?? $photo['url'] }}"
                          data-photo-remote="{{ ($photo['remote'] ?? false) ? '1' : '0' }}">
                          <img src="{{ $photo['url'] }}" alt="{{ $photo['label'] }}" class="inspection-photo-thumb">
                          <span class="inspection-photo-label">{{ $photo['label'] }}</span>
                        </button>
                      @endforeach
                    </div>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="inspection-mobile-list d-md-none">
          @foreach($items as $item)
            <article class="inspection-mobile-card">
              <header class="inspection-mobile-card__header">
                <div>
                  <h6 class="inspection-mobile-card__title">
                    {{ $item->item_name }}
                    @if($item->safety_critical)
                      <span class="badge bg-label-danger ms-1 align-middle" title="Safety Critical">!</span>
                    @endif
                    @if($item->compliance_item)
                      <span class="badge bg-label-warning ms-1 align-middle" title="Compliance">C</span>
                    @endif
                  </h6>
                  @if($item->item_description)
                    <p class="inspection-mobile-card__description">{{ $item->item_description }}</p>
                  @endif
                </div>
                <div class="inspection-mobile-card__result">
                  @switch($item->result)
                    @case('pass') <span class="badge bg-success">Pass</span> @break
                    @case('fail') <span class="badge bg-danger">Fail</span> @break
                    @case('na') <span class="badge bg-secondary">N/A</span> @break
                    @default <span class="badge bg-secondary">Pending</span>
                  @endswitch
                </div>
              </header>

              <dl class="inspection-mobile-card__meta">
                <div>
                  <dt>Severity</dt>
                  <dd>
                    @if($item->defect_severity && $item->defect_severity !== 'none')
                      @switch($item->defect_severity)
                        @case('critical') <span class="badge bg-danger">Critical</span> @break
                        @case('major') <span class="badge bg-warning">Major</span> @break
                        @case('minor') <span class="badge bg-info">Minor</span> @break
                      @endswitch
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </dd>
                </div>
                <div>
                  <dt>Details</dt>
                  <dd>{{ $item->measurement_value ?? '-' }}</dd>
                </div>
                <div>
                  <dt>Notes</dt>
                  <dd>
                    @if($item->defect_notes)
                      {{ Str::limit($item->defect_notes, 100) }}
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </dd>
                </div>
              </dl>

              @php
                $mobilePhotos = $item->photo_gallery ?? [];
              @endphp

              @if(!empty($mobilePhotos))
                <div class="inspection-mobile-card__photos">
                  @foreach($mobilePhotos as $photo)
                    <button
                      type="button"
                      class="inspection-photo-trigger"
                      data-bs-toggle="modal"
                      data-bs-target="#inspectionPhotoModal"
                      data-photo-src="{{ $photo['url'] }}"
                      data-photo-label="{{ $photo['label'] }}"
                      data-photo-download="{{ $photo['download_url'] ?? $photo['url'] }}"
                      data-photo-remote="{{ ($photo['remote'] ?? false) ? '1' : '0' }}">
                      <img src="{{ $photo['url'] }}" alt="{{ $photo['label'] }}" class="inspection-photo-thumb">
                      <span class="inspection-photo-label">{{ $photo['label'] }}</span>
                    </button>
                  @endforeach
                </div>
              @endif
            </article>
          @endforeach
        </div>
      </div>
    </div>
    @endforeach

    <!-- Complete Inspection Form -->
    @if($inspection->status === 'in_progress')
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Complete Inspection</h6>
      </div>
      <div class="card-body">
        <form action="{{ route('inspections.complete', $inspection) }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Inspector Notes</label>
            <textarea name="inspector_notes" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Defects Summary</label>
            <textarea name="defects_summary" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Recommendations</label>
            <textarea name="recommendations" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Next Inspection Due</label>
            <input type="date" name="next_inspection_due" class="form-control" value="{{ now()->addMonth()->format('Y-m-d') }}">
          </div>
          <button type="submit" class="btn btn-primary">Complete Inspection</button>
        </form>
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-12 col-lg-4">
    <!-- Statistics Card -->
    <div class="card mb-4">
      <div class="card-body">
        <h6 class="card-title">Statistics</h6>
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between mb-2">
            <span>Total Items:</span>
            <strong>{{ $statistics['total_items'] }}</strong>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Items Checked:</span>
            <strong>{{ $statistics['items_checked'] }}</strong>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Passed:</span>
            <span class="badge bg-success">{{ $statistics['items_passed'] }}</span>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Failed:</span>
            <span class="badge bg-danger">{{ $statistics['items_failed'] }}</span>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Critical Defects:</span>
            <span class="badge bg-danger">{{ $statistics['critical_defects'] }}</span>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Major Defects:</span>
            <span class="badge bg-warning">{{ $statistics['major_defects'] }}</span>
          </li>
          <li class="d-flex justify-content-between">
            <span>Minor Defects:</span>
            <span class="badge bg-info">{{ $statistics['minor_defects'] }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Actions Card -->
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Actions</h6>
        <div class="d-grid gap-2 inspection-actions-grid">
          @if($inspection->status === 'pending')
            <form action="{{ route('inspections.start', $inspection) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-primary w-100">Start Inspection</button>
            </form>
          @endif

          @if($inspection->status === 'completed')
            <form action="{{ route('inspections.approve', $inspection) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-success w-100">Approve</button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
          @endif

          @if(in_array($inspection->status, ['pending', 'in_progress']))
            <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-outline-primary">Edit Details</a>
          @endif

          @if($vehicle)
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">View Vehicle</a>
          @else
            <div class="text-muted small text-center">Vehicle record no longer available.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Photo Preview Modal -->
<div class="modal fade" id="inspectionPhotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Inspection Photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-3 p-sm-4">
        <img src="" alt="" id="inspectionPhotoModalImage" class="img-fluid rounded shadow-sm">
      </div>
      <div class="modal-footer">
        <a href="#" id="inspectionPhotoModalDownload" class="btn btn-primary" download>
          <i class="ti ti-download me-1"></i>Download
        </a>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('inspections.reject', $inspection) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reject Inspection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Rejection Reason *</label>
            <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject Inspection</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('page-style')
<style>
  .inspection-details-page .card {
    border-radius: 1rem;
  }

  .inspection-photo-trigger {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex: 1 1 110px;
    min-width: 96px;
    max-width: 160px;
    padding: 0.7rem;
    border-radius: 0.9rem;
    border: 1px solid rgba(148, 163, 184, 0.25);
    background: rgba(248, 250, 252, 0.12);
    color: inherit;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .inspection-photo-trigger:hover,
  .inspection-photo-trigger:focus {
    border-color: rgba(59, 130, 246, 0.45);
    background: rgba(59, 130, 246, 0.12);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
    text-decoration: none;
  }

  .inspection-photo-thumb {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: 0.75rem;
    border: 1px solid rgba(148, 163, 184, 0.35);
    background-color: rgba(15, 23, 42, 0.1);
  }

  .inspection-photo-label {
    display: block;
    text-align: center;
    font-size: 0.75rem;
    line-height: 1.15;
    color: var(--bs-body-color, rgba(15, 23, 42, 0.75));
    opacity: 0.85;
  }

  .inspection-mobile-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  @media (min-width: 768px) {
    .table .inspection-photo-trigger {
      flex: 0 0 auto;
      min-width: 86px;
      max-width: 120px;
    }
  }

  .inspection-mobile-card {
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 1rem;
    padding: 1rem;
    background: rgba(248, 250, 252, 0.4);
    backdrop-filter: blur(2px);
  }

  .inspection-mobile-card__header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
  }

  .inspection-mobile-card__result {
    flex-shrink: 0;
    display: flex;
    align-items: flex-start;
  }

  .inspection-mobile-card__title {
    margin-bottom: 0.35rem;
  }

  .inspection-mobile-card__description {
    margin-bottom: 0;
    font-size: 0.85rem;
    color: rgba(51, 65, 85, 0.8);
  }

  .inspection-mobile-card__meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
    gap: 0.75rem;
    margin: 1rem 0 0.5rem;
  }

  .inspection-mobile-card__meta dt {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgba(100, 116, 139, 0.85);
    margin-bottom: 0.15rem;
  }

  .inspection-mobile-card__meta dd {
    margin: 0;
    font-size: 0.95rem;
  }

  .inspection-mobile-card__photos {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.75rem;
  }

  .inspection-actions-grid .btn {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }

  #inspectionPhotoModalImage {
    max-height: 75vh;
    max-width: 100%;
    width: auto;
    display: block;
    margin: 0 auto;
    border-radius: 0.75rem;
  }

  @media (max-width: 991.98px) {
    .inspection-photo-trigger {
      flex: 1 1 120px;
      min-width: 88px;
    }
  }

  @media (max-width: 767.98px) {
    .inspection-details-page .card {
      margin-bottom: 1.25rem;
    }

    .inspection-details-page .card-header,
    .inspection-details-page .card-body {
      padding: 1rem;
    }

    .inspection-mobile-card__meta {
      grid-template-columns: 1fr 1fr;
    }

    #inspectionPhotoModalImage {
      max-height: calc(100vh - 11rem);
    }
  }

  @media (max-width: 575.98px) {
    .inspection-mobile-card__meta {
      grid-template-columns: 1fr;
    }

    .inspection-photo-trigger {
      flex: 1 1 45%;
      max-width: 48%;
    }
  }
</style>
@endpush

@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('inspectionPhotoModal');
    if (!modalEl) {
      return;
    }

    modalEl.addEventListener('show.bs.modal', event => {
      const trigger = event.relatedTarget;
      if (!trigger) {
        return;
      }

      const src = trigger.getAttribute('data-photo-src');
      const label = trigger.getAttribute('data-photo-label') || 'Inspection photo';

      const imageEl = modalEl.querySelector('#inspectionPhotoModalImage');
      const titleEl = modalEl.querySelector('.modal-title');
      const downloadEl = modalEl.querySelector('#inspectionPhotoModalDownload');

      if (imageEl) {
        // Clear previous image first
        imageEl.src = '';

        // Add cache-busting timestamp to force fresh request
        const cacheBustUrl = src + (src.includes('?') ? '&' : '?') + '_t=' + Date.now();

        // Set new source
        imageEl.src = cacheBustUrl;
        imageEl.alt = label;
      }
      if (titleEl) {
        titleEl.textContent = label;
      }
      if (downloadEl) {
        const downloadHref = trigger.getAttribute('data-photo-download') || src;
        const isRemote = trigger.getAttribute('data-photo-remote') === '1';
        downloadEl.href = downloadHref;

        if (isRemote) {
          downloadEl.removeAttribute('download');
          downloadEl.setAttribute('target', '_blank');
        } else {
          downloadEl.removeAttribute('target');
          const safeName = label.replace(/[^\w\d\-]+/g, '_');
          try {
            const url = new URL(downloadHref, window.location.origin);
            const pathname = url.pathname;
            const extension = pathname.lastIndexOf('.') !== -1 ? pathname.substring(pathname.lastIndexOf('.') + 1) : 'jpg';
            downloadEl.setAttribute('download', `${safeName || 'inspection-photo'}.${extension}`);
          } catch (e) {
            downloadEl.setAttribute('download', `${safeName || 'inspection-photo'}.jpg`);
          }
        }
      }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
      const imageEl = modalEl.querySelector('#inspectionPhotoModalImage');
      if (imageEl) {
        imageEl.src = '';
        imageEl.alt = '';
      }
      const downloadEl = modalEl.querySelector('#inspectionPhotoModalDownload');
      if (downloadEl) {
        downloadEl.removeAttribute('download');
        downloadEl.removeAttribute('href');
        downloadEl.removeAttribute('target');
      }
    });
  });
</script>
@endpush
