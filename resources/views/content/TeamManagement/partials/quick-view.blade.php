{{-- Employee Quick View Content Partial --}}
{{-- This partial is loaded via AJAX when the quick view modal is opened --}}

<div class="employee-card">
  {{-- Header --}}
  <div class="employee-card__header d-flex align-items-center gap-3 mb-4">
    <div class="avatar avatar-lg">
      <span class="avatar-initial rounded" style="background: var(--sensei-accent-soft); color: var(--sensei-accent); font-size: 1.25rem; font-weight: 600;">
        {{ strtoupper(substr($member['name'], 0, 2)) }}
      </span>
    </div>
    <div>
      <h4 class="employee-name mb-1">{{ $member['name'] }}</h4>
      <p class="contact-email mb-0">
        <a href="mailto:{{ $member['email'] }}" style="color: var(--sensei-accent); text-decoration: none;">
          {{ $member['email'] }}
        </a>
      </p>
    </div>
  </div>

  {{-- Metadata Grid --}}
  <div class="metadata-grid mb-4">
    <div class="metadata-row">
      <span class="metadata-label">Employee ID</span>
      <span class="metadata-value fw-semibold">{{ $member['employee_id'] }}</span>
    </div>
    <div class="metadata-row">
      <span class="metadata-label">Status</span>
      <span class="metadata-value">
        <span class="status-badge status-{{ strtolower(str_replace('_', '-', $member['status'])) }}">
          {{ ucfirst(str_replace('_', ' ', $member['status'])) }}
        </span>
      </span>
    </div>
    <div class="metadata-row">
      <span class="metadata-label">Branch</span>
      <span class="metadata-value fw-semibold">{{ $member['branch_name'] }}</span>
    </div>
    <div class="metadata-row">
      <span class="metadata-label">Role</span>
      <span class="metadata-value fw-semibold">{{ ucfirst(str_replace('_', ' ', $member['role'])) }}</span>
    </div>
  </div>

  {{-- Contact Section --}}
  <section class="contact-section mb-4">
    <h6 class="section-title mb-3" style="color: var(--sensei-text-primary); font-weight: 600;">Contact Information</h6>
    <div class="metadata-row">
      <span class="metadata-label">Email</span>
      <span class="contact-email metadata-value">
        <a href="mailto:{{ $member['email'] }}" style="color: var(--sensei-accent); text-decoration: none;">
          {{ $member['email'] }}
        </a>
      </span>
    </div>
    @if($member['phone'])
    <div class="metadata-row">
      <span class="metadata-label">Phone</span>
      <span class="contact-phone metadata-value">
        <a href="tel:{{ $member['phone'] }}" style="color: var(--sensei-text-primary); text-decoration: none;">
          {{ $member['phone'] }}
        </a>
      </span>
    </div>
    @endif
  </section>

  {{-- Activity Summary --}}
  <section class="activity-summary mb-4">
    <h6 class="section-title mb-3" style="color: var(--sensei-text-primary); font-weight: 600;">Activity Summary</h6>
    <div class="row g-3">
      <div class="col-6">
        <div class="metric-pill" style="background: var(--sensei-surface-strong); border: 1px solid var(--sensei-border); border-radius: var(--sensei-radius); padding: 1rem; text-align: center;">
          <span class="metric-label d-block mb-2" style="color: var(--sensei-text-tertiary); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Incidents</span>
          <span class="metric-value d-block" style="color: var(--sensei-accent); font-size: 1.75rem; font-weight: 700;">{{ $member['incidents_count'] ?? 0 }}</span>
        </div>
      </div>
      <div class="col-6">
        <div class="metric-pill" style="background: var(--sensei-surface-strong); border: 1px solid var(--sensei-border); border-radius: var(--sensei-radius); padding: 1rem; text-align: center;">
          <span class="metric-label d-block mb-2" style="color: var(--sensei-text-tertiary); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Certifications</span>
          <span class="metric-value d-block" style="color: var(--sensei-accent); font-size: 1.75rem; font-weight: 700;">{{ $member['certifications_count'] ?? 0 }}</span>
        </div>
      </div>
    </div>
    <div class="metadata-row mt-3">
      <span class="metadata-label">Last Active</span>
      <span class="metadata-value">{{ $member['last_active_human'] ?? 'No data' }}</span>
    </div>
  </section>

  {{-- Vehicle Assignment --}}
  @if(!empty($member['current_vehicle']))
  <section class="vehicle-section">
    <div style="background: var(--sensei-accent-soft); border: 1px solid color-mix(in srgb, var(--sensei-accent) 25%, transparent); border-radius: var(--sensei-radius); padding: 1rem;">
      <strong style="color: var(--sensei-accent); display: block; margin-bottom: 0.5rem;">Currently Assigned Vehicle</strong>
      <span style="color: var(--sensei-text-primary);">
        {{ $member['current_vehicle']['registration_number'] }} -
        {{ $member['current_vehicle']['make'] }} {{ $member['current_vehicle']['model'] }}
      </span>
    </div>
  </section>
  @endif
</div>
