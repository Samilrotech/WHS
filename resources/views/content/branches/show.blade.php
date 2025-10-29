@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', $branch->name . ' - Branch Details')

@section('content')
<div class="whs-shell">
  <x-whs.hero
    eyebrow="Administration"
    :title="$branch->name"
    :subtitle="$branch->address . ', ' . $branch->city . ', ' . $branch->state . ' ' . $branch->postcode"
    :metric="true"
    metricLabel="Total Employees"
    :metricValue="$statistics['total_employees'] ?? 0"
    metricCaption="Assigned to this branch"
    :createRoute="route('teams.create')"
    createLabel="Add Employee"
  />

  <x-whs.breadcrumb
    :items="[
      ['label' => 'Branch Management', 'url' => route('branches.index')],
      ['label' => $branch->name],
    ]"
    class="mb-6"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-users"
      iconVariant="brand"
      label="Total Employees"
      :value="$statistics['total_employees'] ?? 0"
      meta="All staff"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active Employees"
      :value="$statistics['active_employees'] ?? 0"
      meta="Currently active"
    />

    <x-whs.metric-card
      icon="ti-user-shield"
      iconVariant="brand"
      label="Managers"
      :value="$statistics['managers'] ?? 0"
      meta="Management roles"
    />

    <x-whs.metric-card
      icon="ti-user"
      iconVariant="warning"
      label="Staff"
      :value="$statistics['employees'] ?? 0"
      meta="Employee roles"
    />
  </section>

  <div class="whs-main">
    <div class="whs-section-heading">
      <div>
        <h2>Branch Information</h2>
        <p>Comprehensive details for {{ $branch->name }} including contact information and operational status.</p>
      </div>
    </div>

    <x-whs.card class="sensei-surface-card">
      <div class="whs-chip-group">
        <span class="whs-chip whs-chip--id">
          <i class="icon-base ti ti-hash"></i>
          {{ $branch->code }}
        </span>
        @if($branch->is_active)
          <span class="whs-chip whs-chip--status-resolved">
            <i class="icon-base ti ti-circle-check"></i>
            Active
          </span>
        @else
          <span class="whs-chip whs-chip--status">
            <i class="icon-base ti ti-circle-x"></i>
            Inactive
          </span>
        @endif
      </div>

      <div class="whs-kv-grid">
        <div class="whs-kv">
          <span class="whs-kv__label">Branch Code</span>
          <span class="whs-kv__value">{{ $branch->code }}</span>
        </div>

        <div class="whs-kv">
          <span class="whs-kv__label">Branch Name</span>
          <span class="whs-kv__value">{{ $branch->name }}</span>
        </div>

        <div class="whs-kv whs-kv--span-2">
          <span class="whs-kv__label">Full Address</span>
          <span class="whs-kv__value whs-kv__value--stack">
            <span>
              <i class="icon-base ti ti-map-pin"></i>
              {{ $branch->address }}
            </span>
            <span>{{ $branch->city }}, {{ $branch->state }} {{ $branch->postcode }}</span>
          </span>
        </div>

        @if($branch->phone)
          <div class="whs-kv">
            <span class="whs-kv__label">Phone</span>
            <span class="whs-kv__value">
              <i class="icon-base ti ti-phone"></i>
              {{ $branch->phone }}
            </span>
          </div>
        @endif

        @if($branch->email)
          <div class="whs-kv">
            <span class="whs-kv__label">Email</span>
            <span class="whs-kv__value">
              <i class="icon-base ti ti-mail"></i>
              {{ $branch->email }}
            </span>
          </div>
        @endif

        @if($branch->manager_name)
          <div class="whs-kv">
            <span class="whs-kv__label">Branch Manager</span>
            <span class="whs-kv__value">
              <i class="icon-base ti ti-user-shield"></i>
              {{ $branch->manager_name }}
            </span>
          </div>
        @endif
      </div>

      <div class="whs-card__actions d-flex flex-wrap gap-2">
        <x-whs.action-button
          :href="route('branches.edit', $branch->id)"
          icon="ti-edit"
        >
          Edit Branch
        </x-whs.action-button>

        <form action="{{ route('branches.toggleStatus', $branch->id) }}" method="POST" class="d-inline">
          @csrf
          <x-whs.action-button
            type="submit"
            icon="{{ $branch->is_active ? 'ti-pause' : 'ti-player-play' }}"
            :variant="$branch->is_active ? 'ghost' : 'success'"
          >
            {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
          </x-whs.action-button>
        </form>

        @if($branch->users_count === 0)
          <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this branch? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <x-whs.action-button type="submit" variant="danger" icon="ti-trash">
              Delete
            </x-whs.action-button>
          </form>
        @else
          <x-whs.action-button variant="danger" icon="ti-trash" disabled>
            Delete
          </x-whs.action-button>
        @endif

        <x-whs.action-button
          :href="route('vehicles.index', ['branch' => $branch->id])"
          icon="ti-car"
        >
          Branch Vehicles
        </x-whs.action-button>

        <x-whs.action-button
          :href="route('inspections.index', ['branch' => $branch->id])"
          icon="ti-file-analytics"
          variant="ghost"
        >
          Inspection Log
        </x-whs.action-button>

        <x-whs.action-button
          :href="route('branches.index')"
          variant="ghost"
          icon="ti-arrow-left"
        >
          Back to List
        </x-whs.action-button>
      </div>
    </x-whs.card>

    <div class="whs-section-heading mt-4">
      <div>
        <h2>Employees at {{ $branch->name }}</h2>
        <p>Staff members currently assigned to this branch location.</p>
      </div>
    </div>

    @if($branch->users->count() > 0)
      <div class="whs-stack">
        @foreach($branch->users as $user)
          <x-whs.card class="sensei-surface-card">
            <div class="whs-chip-group">
              <span class="whs-chip whs-chip--id">
                <i class="icon-base ti ti-id"></i>
                {{ $user->employee_id ?? 'N/A' }}
              </span>
              @foreach($user->getRoleNames() as $role)
                <span class="whs-chip whs-chip--status">{{ $role }}</span>
              @endforeach
              @if($user->is_active)
                <span class="whs-chip whs-chip--status-resolved">
                  <i class="icon-base ti ti-circle-check"></i>
                  Active
                </span>
              @else
                <span class="whs-chip whs-chip--status">
                  <i class="icon-base ti ti-circle-x"></i>
                  Inactive
                </span>
              @endif
            </div>

            <h3 class="whs-branch-card__title">{{ $user->name }}</h3>

            <div class="whs-kv-grid">
              @if($user->position)
                <div class="whs-kv">
                  <span class="whs-kv__label">Position</span>
                  <span class="whs-kv__value">{{ $user->position }}</span>
                </div>
              @endif

              <div class="whs-kv">
                <span class="whs-kv__label">Email</span>
                <span class="whs-kv__value">
                  <i class="icon-base ti ti-mail"></i>
                  {{ $user->email }}
                </span>
              </div>
            </div>

            <div class="whs-card__actions d-flex flex-wrap gap-2">
              <x-whs.action-button :href="route('teams.show', $user->id)" icon="ti-external-link">
                View Profile
              </x-whs.action-button>
              <x-whs.action-button :href="route('teams.edit', $user->id)" variant="ghost" icon="ti-edit">
                Edit
              </x-whs.action-button>
              <x-whs.action-button href="mailto:{{ $user->email }}" variant="ghost" icon="ti-mail">
                Email
              </x-whs.action-button>
            </div>
          </x-whs.card>
        @endforeach
      </div>
    @else
      <div class="whs-empty">
        <div class="whs-empty__icon">
          <i class="icon-base ti ti-users"></i>
        </div>
        <h3>No Employees Assigned</h3>
        <p>This branch currently has no employees. Add your first team member to get started.</p>
        <a href="{{ route('teams.create') }}" class="whs-btn-primary mt-3">
          <i class="icon-base ti ti-user-plus"></i>
          <span>Add First Employee</span>
        </a>
      </div>
    @endif
  </div>
</div>
@endsection

