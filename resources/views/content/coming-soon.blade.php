@extends('layouts.layoutMaster')

@section('title', $pageTitle ?? 'Coming Soon')

@section('content')
<div class="container-xxl container-p-y">
  <div class="misc-wrapper text-center">
    <h2 class="mb-2 mx-2">{{ $heading ?? 'Under Construction' }}</h2>
    <p class="mb-6 mx-2">{{ $message ?? 'This feature is currently under development and will be available soon.' }}</p>

    @if(isset($features) && count($features) > 0)
    <div class="card mb-6">
      <div class="card-body">
        <h5 class="card-title mb-4">Planned Features</h5>
        <ul class="list-unstyled text-start" style="max-width: 600px; margin: 0 auto;">
          @foreach($features as $feature)
          <li class="mb-2">
            <i class="icon-base ti ti-check text-success me-2"></i>
            {{ $feature }}
          </li>
          @endforeach
        </ul>
      </div>
    </div>
    @endif

    <a href="{{ $backUrl ?? url()->previous() }}" class="btn btn-primary">
      <i class="icon-base ti ti-arrow-left me-1"></i>
      Back to {{ $backText ?? 'Previous Page' }}
    </a>

    <div class="mt-6">
      <img src="{{ asset('assets/img/illustrations/misc-under-maintenance-light.png') }}"
           alt="Under Construction"
           width="500"
           class="img-fluid"
           data-app-light-img="illustrations/misc-under-maintenance-light.png"
           data-app-dark-img="illustrations/misc-under-maintenance-dark.png">
    </div>
  </div>
</div>
@endsection

