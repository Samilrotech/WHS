@extends('layouts/layoutMaster')

@section('title', 'Create Branch')

@section('content')
@php
    $stateOptions = config('branch.states');
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Administration"
    title="Create Branch"
    subtitle="Add a new branch location to your organization and configure its primary settings."
  />

  <x-whs.breadcrumb
    :items="[
      ['label' => 'Branch Management', 'url' => route('branches.index')],
      ['label' => 'Create Branch'],
    ]"
    class="mb-6"
  />

  <x-whs.card>
    <form action="{{ route('branches.store') }}" method="POST" class="whs-form">
      @csrf

      <x-whs.forms.section
        title="Branch Information"
        description="Primary identifiers that distinguish this branch across your organization."
      >
        <x-whs.forms.input
          name="name"
          label="Branch Name"
          placeholder="e.g., Sydney Office"
          required
          :value="old('name')"
          :error="$errors->first('name')"
        />

        <x-whs.forms.input
          name="code"
          label="Branch Code"
          placeholder="e.g., SYD"
          maxlength="50"
          required
          :value="old('code')"
          :error="$errors->first('code')"
          help="Unique identifier for this branch (e.g., SYD, MEL, BRI)."
        />
      </x-whs.forms.section>

      <x-whs.forms.section
        title="Address Details"
        description="Where the branch is located and how field teams can find it."
      >
        <x-whs.forms.textarea
          name="address"
          label="Street Address"
          rows="3"
          placeholder="Enter full street address"
          required
          :value="old('address')"
          :error="$errors->first('address')"
        />

        <x-whs.forms.input
          name="city"
          label="City"
          placeholder="e.g., Sydney"
          required
          :value="old('city')"
          :error="$errors->first('city')"
        />

        <x-whs.forms.select
          name="state"
          label="State"
          placeholder="Select state"
          required
          :options="$stateOptions"
          :value="old('state')"
          :error="$errors->first('state')"
        />

        <x-whs.forms.input
          name="postcode"
          label="Postcode"
          placeholder="e.g., 2000"
          maxlength="10"
          required
          :value="old('postcode')"
          :error="$errors->first('postcode')"
        />
      </x-whs.forms.section>

      <x-whs.forms.section
        title="Contact Information"
        description="Keep your branch reachable for coordination and emergency response."
        :columns="3"
      >
        <x-whs.forms.input
          name="phone"
          label="Phone Number"
          type="tel"
          placeholder="e.g., (02) 9XXX XXXX"
          :value="old('phone')"
          :error="$errors->first('phone')"
        />

        <x-whs.forms.input
          name="email"
          label="Email Address"
          type="email"
          placeholder="e.g., sydney@example.com"
          :value="old('email')"
          :error="$errors->first('email')"
        />

        <x-whs.forms.input
          name="manager_name"
          label="Branch Manager"
          placeholder="Manager's full name"
          :value="old('manager_name')"
          :error="$errors->first('manager_name')"
        />
      </x-whs.forms.section>

      <x-whs.forms.section columns="1">
        <x-whs.forms.toggle
          name="is_active"
          label="Active Branch"
          description="Inactive branches cannot have new employees assigned."
          :checked="old('is_active', true)"
          :error="$errors->first('is_active')"
        />
      </x-whs.forms.section>

      <div class="whs-form__actions">
        <button type="submit" class="whs-btn-primary">
          <i class="icon-base ti ti-check"></i>
          <span>Create Branch</span>
        </button>

        <x-whs.action-button
          :href="route('branches.index')"
          variant="ghost"
          icon="ti-arrow-left"
        >
          Cancel
        </x-whs.action-button>
      </div>
    </form>
  </x-whs.card>
</div>
@endsection

