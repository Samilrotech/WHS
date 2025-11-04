@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
  $queryParams = array_filter(['q' => request('q')]);
  $statusFilters = [
    [
      'label' => 'All',
      'url' => route('branches.index', $queryParams),
      'active' => !request()->filled('status'),
    ],
    [
      'label' => 'Active',
      'url' => route('branches.index', array_merge($queryParams, ['status' => 'active'])),
      'active' => request('status') === 'active',
    ],
    [
      'label' => 'Inactive',
      'url' => route('branches.index', array_merge($queryParams, ['status' => 'inactive'])),
      'active' => request('status') === 'inactive',
    ],
  ];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Branch Management')

@section('content')
<div class="whs-shell">
  <x-whs.hero
    eyebrow="Administration"
    title="Branch Management"
    subtitle="Enterprise-grade command centre for organizational locations. Centralized branch oversight, employee allocation, and geographic operations management across your entire network."
    :metric="true"
    metricLabel="Total Locations"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Operational facilities"
    :searchRoute="route('branches.index')"
    searchPlaceholder="Search branches by name, code, or location..."
    :createRoute="route('branches.create')"
    createLabel="Add Branch"
    :filters="$statusFilters"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-building"
      iconVariant="brand"
      label="Total Branches"
      :value="$statistics['total'] ?? 0"
      meta="All locations"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active Branches"
      :value="$statistics['active'] ?? 0"
      meta="Operational facilities"
    />

    <x-whs.metric-card
      icon="ti-circle-x"
      iconVariant="warning"
      label="Inactive Branches"
      :value="$statistics['inactive'] ?? 0"
      meta="Non-operational"
    />

    <x-whs.metric-card
      icon="ti-users"
      iconVariant="brand"
      label="Total Employees"
      :value="$statistics['total_employees'] ?? 0"
      meta="Across all branches"
    />
  </section>

  <div class="whs-layout whs-layout--full-width">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Branch Directory</h2>
          <p>Comprehensive list of all organizational locations with employee allocation and operational status.</p>
        </div>
      </div>

      {{-- Dense Table View --}}
      @include('content.branches._table-view')
    </div>
  </div>
</div>
@endsection

