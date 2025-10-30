<?php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
?>



<?php $__env->startSection('title', 'Vehicle Management'); ?>

<?php $__env->startSection('page-script'); ?>
<script>
  (function ensureWideLayout() {
    var expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);
    document.cookie = 'contentLayout=wide;path=/;expires=' + expires.toUTCString();
  })();
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.sections.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
  $activeVehicles = $statistics['active'] ?? 0;
  $maintenanceVehicles = $statistics['maintenance'] ?? 0;
  $inspectionDue = $statistics['inspection_due'] ?? 0;

  $filterPills = [
    ['label' => 'All vehicles', 'active' => true],
    ['label' => 'Active', 'active' => $activeVehicles > 0],
    ['label' => 'Maintenance', 'active' => $maintenanceVehicles > 0],
    ['label' => 'Inspection Due', 'active' => $inspectionDue > 0],
  ];
?>

<div class="whs-shell">
  <?php if (isset($component)) { $__componentOriginal745933b6b17e3f95eace2214fb06a9b7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal745933b6b17e3f95eace2214fb06a9b7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.hero','data' => ['eyebrow' => 'Fleet & Assets','title' => 'Vehicle Management','subtitle' => 'Fleet tracking with automated inspection reminders, service history, and cost analysis per vehicle across all branches.','metric' => true,'metricLabel' => 'Total fleet','metricValue' => $statistics['total'] ?? 0,'metricCaption' => 'Vehicles tracked across WHS4 network','searchRoute' => route('vehicles.index'),'searchPlaceholder' => 'Search vehicles, registration, make…','createRoute' => route('vehicles.create'),'createLabel' => 'Add vehicle','filters' => $filterPills]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['eyebrow' => 'Fleet & Assets','title' => 'Vehicle Management','subtitle' => 'Fleet tracking with automated inspection reminders, service history, and cost analysis per vehicle across all branches.','metric' => true,'metricLabel' => 'Total fleet','metricValue' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['total'] ?? 0),'metricCaption' => 'Vehicles tracked across WHS4 network','searchRoute' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('vehicles.index')),'searchPlaceholder' => 'Search vehicles, registration, make…','createRoute' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('vehicles.create')),'createLabel' => 'Add vehicle','filters' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filterPills)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal745933b6b17e3f95eace2214fb06a9b7)): ?>
<?php $attributes = $__attributesOriginal745933b6b17e3f95eace2214fb06a9b7; ?>
<?php unset($__attributesOriginal745933b6b17e3f95eace2214fb06a9b7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal745933b6b17e3f95eace2214fb06a9b7)): ?>
<?php $component = $__componentOriginal745933b6b17e3f95eace2214fb06a9b7; ?>
<?php unset($__componentOriginal745933b6b17e3f95eace2214fb06a9b7); ?>
<?php endif; ?>

  <section class="whs-metrics">
    <?php if (isset($component)) { $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-car','iconVariant' => 'brand','label' => 'Total Vehicles','value' => $statistics['total'] ?? 0,'meta' => 'Complete fleet inventory']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-car','iconVariant' => 'brand','label' => 'Total Vehicles','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['total'] ?? 0),'meta' => 'Complete fleet inventory']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $attributes = $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $component = $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-circle-check','iconVariant' => 'success','label' => 'Active','value' => $activeVehicles,'meta' => 'Operational and ready','metaClass' => 'text-success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-circle-check','iconVariant' => 'success','label' => 'Active','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeVehicles),'meta' => 'Operational and ready','metaClass' => 'text-success']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $attributes = $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $component = $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-tool','iconVariant' => 'warning','label' => 'Maintenance','value' => $maintenanceVehicles,'meta' => 'Currently being serviced','metaClass' => 'text-warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-tool','iconVariant' => 'warning','label' => 'Maintenance','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($maintenanceVehicles),'meta' => 'Currently being serviced','metaClass' => 'text-warning']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $attributes = $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $component = $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-alert-triangle','iconVariant' => 'critical','label' => 'Inspection Due','value' => $inspectionDue,'meta' => 'Requires immediate attention','metaClass' => 'text-danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-alert-triangle','iconVariant' => 'critical','label' => 'Inspection Due','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inspectionDue),'meta' => 'Requires immediate attention','metaClass' => 'text-danger']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $attributes = $__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__attributesOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6)): ?>
<?php $component = $__componentOriginalc7810d54eb7db540ba004a2cd6dccda6; ?>
<?php unset($__componentOriginalc7810d54eb7db540ba004a2cd6dccda6); ?>
<?php endif; ?>
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Vehicle fleet register</h2>
          <p>Fleet inventory sorted by registration number.</p>
        </div>
        <span class="whs-updated">Updated <?php echo e(now()->format('H:i')); ?></span>
      </div>

      <form method="GET" class="card sensei-surface-card sensei-filter-card mb-4 border-0 p-3">
        <div class="row g-3 align-items-end">
          <div class="col-lg-3">
            <label for="filter_branch" class="form-label">Branch</label>
            <select id="filter_branch" name="branch" class="form-select">
              <option value="">All branches</option>
              <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($branch->id); ?>" <?php echo e(($filters['branch'] ?? '') == $branch->id ? 'selected' : ''); ?>><?php echo e($branch->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-lg-3">
            <label for="filter_status" class="form-label">Status</label>
            <select id="filter_status" name="status" class="form-select">
              <option value="">All statuses</option>
              <option value="active" <?php echo e(($filters['status'] ?? '') === 'active' ? 'selected' : ''); ?>>Active</option>
              <option value="maintenance" <?php echo e(($filters['status'] ?? '') === 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
              <option value="inactive" <?php echo e(($filters['status'] ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
              <option value="sold" <?php echo e(($filters['status'] ?? '') === 'sold' ? 'selected' : ''); ?>>Sold</option>
            </select>
          </div>
          <div class="col-lg-3">
            <label for="filter_assigned" class="form-label">Assignment</label>
            <select id="filter_assigned" name="assigned" class="form-select">
              <option value="all" <?php echo e(($filters['assigned'] ?? 'all') === 'all' ? 'selected' : ''); ?>>All vehicles</option>
              <option value="yes" <?php echo e(($filters['assigned'] ?? '') === 'yes' ? 'selected' : ''); ?>>Assigned</option>
              <option value="no" <?php echo e(($filters['assigned'] ?? '') === 'no' ? 'selected' : ''); ?>>Available</option>
            </select>
          </div>
          <div class="col-lg-3">
            <label for="filter_make" class="form-label">Make</label>
            <select id="filter_make" name="make" class="form-select">
              <option value="">All makes</option>
              <?php $__currentLoopData = $makes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $make): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($make); ?>" <?php echo e(($filters['make'] ?? '') === $make ? 'selected' : ''); ?>><?php echo e($make); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-12 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary">Apply filters</button>
            <a href="<?php echo e(route('vehicles.index')); ?>" class="btn btn-outline-secondary">Reset</a>
          </div>
        </div>
      </form>

      <div class="whs-card-list">
        <?php $__empty_1 = true; $__currentLoopData = $vehicles['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
            $severity = $vehicle->isInspectionDue() || $vehicle->isRegistrationExpiring() ? 'critical' : ($vehicle->status === 'maintenance' ? 'high' : 'low');
            $statusLabel = match($vehicle->status) {
              'active' => 'Active',
              'maintenance' => 'Maintenance',
              'inactive' => 'Inactive',
              default => ucfirst($vehicle->status)
            };
            $latestInspection = $vehicle->latestInspection;
            if ($latestInspection && in_array($latestInspection->overall_result, ['fail_major', 'fail_critical'])) {
              $severity = 'critical';
            }
            $assignedDriver = $vehicle->currentAssignment?->user;
          ?>

          <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => ['severity' => $severity,'class' => 'sensei-surface-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($severity),'class' => 'sensei-surface-card']); ?>
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id"><?php echo e($vehicle->registration_number); ?></span>
              <span class="whs-chip whs-chip--status whs-chip--status-<?php echo e(strtolower($statusLabel)); ?>">
                <?php echo e($statusLabel); ?>

              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3><?php echo e($vehicle->make); ?> <?php echo e($vehicle->model); ?></h3>
                <p><?php echo e($vehicle->year); ?> • <?php echo e(number_format($vehicle->odometer_reading)); ?> km</p>
              </div>
              <div>
                <span class="whs-location-label">Registration Expiry</span>
                <span <?php if($vehicle->isRegistrationExpiring()): ?> class="text-danger" <?php endif; ?>>
                  <?php if($vehicle->rego_expiry_date): ?>
                    <?php echo e($vehicle->rego_expiry_date->format('d M Y')); ?>

                    <?php if($vehicle->isRegistrationExpiring()): ?>
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Expiring</span>
                    <?php endif; ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </span>
              </div>
              <div>
                <span class="whs-location-label">Inspection Due</span>
                <span <?php if($vehicle->isInspectionDue()): ?> class="text-danger" <?php endif; ?>>
                  <?php if($vehicle->inspection_due_date): ?>
                    <?php echo e($vehicle->inspection_due_date->format('d M Y')); ?>

                    <?php if($vehicle->isInspectionDue()): ?>
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Overdue</span>
                    <?php endif; ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </span>
              </div>
              <div>
                <span class="whs-location-label">Assignment</span>
                <span>
                  <?php if($vehicle->isAssigned() && $assignedDriver): ?>
                    <strong><?php echo e($assignedDriver->name); ?></strong> &middot; since <?php echo e(optional($vehicle->currentAssignment->assigned_date)->diffForHumans()); ?>

                  <?php elseif($vehicle->isAssigned()): ?>
                    Assigned
                  <?php else: ?>
                    <span class="text-muted">Available for allocation</span>
                  <?php endif; ?>
                </span>
              </div>
              <div>
                <span class="whs-location-label">Last Inspection</span>
                <span>
                  <?php if($latestInspection): ?>
                    <?php
                      $inspectionResult = $latestInspection->overall_result ?? $latestInspection->status;
                      $badgeColor = in_array($inspectionResult, ['fail_major','fail_critical']) ? 'danger' : (in_array($inspectionResult, ['pass','pass_minor']) ? 'success' : 'info');
                    ?>
                    <?php echo e($latestInspection->inspection_date?->format('d M Y')); ?>

                    <span class="badge bg-label-<?php echo e($badgeColor); ?> ms-1"><?php echo e(ucfirst(str_replace('_', ' ', $inspectionResult))); ?></span>
                  <?php else: ?>
                    <span class="text-muted">No inspections logged</span>
                  <?php endif; ?>
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="<?php echo e(route('vehicles.show', $vehicle)); ?>" class="whs-action-btn" aria-label="View vehicle">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <a href="<?php echo e(route('vehicles.edit', $vehicle)); ?>" class="whs-action-btn" aria-label="Edit vehicle">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                <?php if(!$vehicle->isAssigned()): ?>
                  <button type="button" class="whs-action-btn" data-bs-toggle="modal" data-bs-target="#assignModal<?php echo e($vehicle->id); ?>">
                    <i class="icon-base ti ti-user-plus"></i>
                    <span>Assign</span>
                  </button>
                <?php else: ?>
                  <button type="button" class="whs-action-btn whs-action-btn--warning" data-bs-toggle="modal" data-bs-target="#returnModal<?php echo e($vehicle->id); ?>">
                    <i class="icon-base ti ti-arrow-back-up"></i>
                    <span>Return</span>
                  </button>
                <?php endif; ?>

                <form action="<?php echo e(route('vehicles.destroy', $vehicle)); ?>" method="POST" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this vehicle?')">
                    <i class="icon-base ti ti-trash"></i>
                    <span>Delete</span>
                  </button>
                </form>
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
           <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5901ded5df0437468c08b7cbbe1dd22)): ?>
<?php $attributes = $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22; ?>
<?php unset($__attributesOriginale5901ded5df0437468c08b7cbbe1dd22); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5901ded5df0437468c08b7cbbe1dd22)): ?>
<?php $component = $__componentOriginale5901ded5df0437468c08b7cbbe1dd22; ?>
<?php unset($__componentOriginale5901ded5df0437468c08b7cbbe1dd22); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-car whs-empty__icon"></i>
              <h3>No vehicles yet</h3>
              <p>No vehicles have been added to the fleet. Start tracking your vehicle inventory.</p>
              <a href="<?php echo e(route('vehicles.create')); ?>" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Add first vehicle
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <aside class="whs-sidebar">
      <?php if (isset($component)) { $__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.sidebar-panel','data' => ['title' => 'Fleet status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.sidebar-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Fleet status']); ?>
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active (Operational)</span>
            <strong class="text-success"><?php echo e($activeVehicles); ?></strong>
          </li>
          <li>
            <span>In Maintenance</span>
            <strong class="text-warning"><?php echo e($maintenanceVehicles); ?></strong>
          </li>
          <li>
            <span>Inspection Due</span>
            <strong class="text-danger"><?php echo e($inspectionDue); ?></strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Monthly automated inspection reminders ensure compliance and safety across the fleet.
        </p>
       <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230)): ?>
<?php $attributes = $__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230; ?>
<?php unset($__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230)): ?>
<?php $component = $__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230; ?>
<?php unset($__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230); ?>
<?php endif; ?>

      <?php if (isset($component)) { $__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.sidebar-panel','data' => ['title' => 'Vehicle maintenance cycle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.sidebar-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Vehicle maintenance cycle']); ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. QR Code Scanning</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Scan vehicle QR for quick access</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Monthly Inspection</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated reminders sent</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Service History</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Complete maintenance tracking</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Cost Analysis</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Per-vehicle expense tracking</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Preventive Scheduling</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated maintenance planning</span>
          </div>
        </div>
       <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230)): ?>
<?php $attributes = $__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230; ?>
<?php unset($__attributesOriginal7ab640e2a1c51aca1ba3ec2a93eea230); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230)): ?>
<?php $component = $__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230; ?>
<?php unset($__componentOriginal7ab640e2a1c51aca1ba3ec2a93eea230); ?>
<?php endif; ?>
    </aside>
  </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.layoutMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\WHS5\resources\views/content/vehicles/index.blade.php ENDPATH**/ ?>