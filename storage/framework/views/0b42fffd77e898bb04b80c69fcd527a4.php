<?php
$configData = Helper::appClasses();
?>



<?php $__env->startSection('title', 'WHS4 Dashboard'); ?>

<?php $__env->startSection('page-script'); ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const style = getComputedStyle(document.documentElement);
  const resolveVar = (token, fallback) => {
    const value = style.getPropertyValue(token);
    return value && value.trim() !== '' ? value.trim() : fallback;
  };

  const accent = resolveVar('--sensei-accent', '#3b82f6');
  const textSecondary = resolveVar('--sensei-text-secondary', '#9ca3af');
  const gridColor = resolveVar('--sensei-gridline', 'rgba(255, 255, 255, 0.08)');
  const success = resolveVar('--sensei-success', '#5eead4');
  const warning = resolveVar('--sensei-warning', '#fbbf24');
  const high = resolveVar('--sensei-alert', '#fb7185');
  const alert = resolveVar('--sensei-alert', '#ef4444');
  const tooltipTheme = document.documentElement.dataset.bsTheme === 'light' ? 'light' : 'dark';

  const incidentTrendChart = document.querySelector('#incidentTrendChart');
  if (incidentTrendChart) {
    const monthlyData = <?php echo json_encode($monthlyIncidents, 15, 512) ?>;
    const months = monthlyData.map(item => item.month);
    const counts = monthlyData.map(item => item.count);

    const options = {
      series: [{ name: 'Incidents', data: counts }],
      chart: { type: 'line', height: 300, toolbar: { show: false } },
      colors: [accent],
      stroke: { curve: 'smooth', width: 3 },
      xaxis: {
        categories: months,
        labels: { style: { colors: textSecondary } },
        axisBorder: { color: gridColor },
        axisTicks: { color: gridColor }
      },
      yaxis: {
        labels: { style: { colors: textSecondary } }
      },
      grid: { borderColor: gridColor, strokeDashArray: 5 },
      tooltip: {
        theme: tooltipTheme,
        y: { formatter: val => `${val} incidents` }
      }
    };

    new ApexCharts(incidentTrendChart, options).render();
  }

  const riskDonutChart = document.querySelector('#riskDonutChart');
  if (riskDonutChart) {
    const riskData = <?php echo json_encode($riskDistribution, 15, 512) ?>;

    const options = {
      series: [riskData.low, riskData.medium, riskData.high, riskData.critical],
      labels: ['Low (1-5)', 'Medium (6-11)', 'High (12-19)', 'Critical (20-25)'],
      chart: { type: 'donut', height: 300 },
      colors: [success, warning, high, alert],
      legend: { position: 'bottom', labels: { colors: textSecondary } },
      dataLabels: {
        enabled: true,
        formatter: val => `${Math.round(val)}%`
      },
      tooltip: {
        theme: tooltipTheme,
        y: { formatter: val => `${val} risks` }
      }
    };

    new ApexCharts(riskDonutChart, options).render();
  }
});
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.sections.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $safetyScore = $stats['total_incidents'] > 0 ? max(0, 100 - ($stats['open_incidents'] * 10)) : 100;
    $openIncidentsLabel = $stats['open_incidents'] === 1 ? 'case' : 'cases';
    $openCaption = 'Based on ' . $stats['open_incidents'] . ' open ' . ($stats['open_incidents'] === 1 ? 'incident' : 'incidents');
    $overdueInspectionsLabel = $stats['inspections_overdue'] === 1 ? 'inspection' : 'inspections';
    $incidentTrend = $stats['incidents_trend'] ?? 0;
    $riskTrend = $stats['risks_trend'] ?? 0;
    $vehicleTrend = $stats['vehicles_trend'] ?? 0;
    $inspectionTrend = $stats['inspections_trend'] ?? 0;
    $activeAlerts = $stats['active_emergency_alerts'] ?? 0;
    $isEmployee = auth()->user()?->hasRole('Employee') ?? false;
?>

<div class="whs-shell">
  <?php if (isset($component)) { $__componentOriginal745933b6b17e3f95eace2214fb06a9b7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal745933b6b17e3f95eace2214fb06a9b7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.hero','data' => ['eyebrow' => 'Safety Dashboard','title' => 'WHS4 Overview','subtitle' => 'Real-time workplace health and safety monitoring across all operations','metric' => true,'metricLabel' => 'Safety Score','metricValue' => $safetyScore . '%','metricCaption' => $openCaption]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['eyebrow' => 'Safety Dashboard','title' => 'WHS4 Overview','subtitle' => 'Real-time workplace health and safety monitoring across all operations','metric' => true,'metric-label' => 'Safety Score','metric-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($safetyScore . '%'),'metric-caption' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($openCaption)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-alert-triangle','iconVariant' => 'critical','label' => 'Total Incidents','value' => $stats['total_incidents'],'meta' => $stats['open_incidents'] . ' open ' . $openIncidentsLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-alert-triangle','iconVariant' => 'critical','label' => 'Total Incidents','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['total_incidents']),'meta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['open_incidents'] . ' open ' . $openIncidentsLabel)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-shield-exclamation','iconVariant' => 'warning','label' => 'High Risk Items','value' => $stats['high_risk_assessments'],'meta' => 'Requires immediate attention']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-shield-exclamation','iconVariant' => 'warning','label' => 'High Risk Items','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['high_risk_assessments']),'meta' => 'Requires immediate attention']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-truck','iconVariant' => 'success','label' => 'Active Vehicles','value' => $stats['vehicles_active'],'meta' => 'of ' . $stats['vehicles_total'] . ' in fleet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-truck','iconVariant' => 'success','label' => 'Active Vehicles','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['vehicles_active']),'meta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('of ' . $stats['vehicles_total'] . ' in fleet')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.metric-card','data' => ['icon' => 'ti-clipboard-check','iconVariant' => 'brand','label' => 'Pending Inspections','value' => $stats['inspections_pending'],'meta' => $stats['inspections_overdue'] . ' overdue ' . $overdueInspectionsLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ti-clipboard-check','iconVariant' => 'brand','label' => 'Pending Inspections','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['inspections_pending']),'meta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['inspections_overdue'] . ' overdue ' . $overdueInspectionsLabel)]); ?>
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

  <?php if($activeAlerts > 0): ?>
    <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => ['severity' => 'critical']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => 'critical']); ?>
      <div class="sensei-alert">
        <div class="sensei-alert__header">
          <div>
            <span class="section-eyebrow">System Notice</span>
            <h2>Active Emergency Alert</h2>
          </div>
          <span class="whs-chip whs-chip--status-critical">
            <i class="icon-base ti ti-alarm"></i>
            <?php echo e($activeAlerts); ?> <?php echo e($activeAlerts === 1 ? 'alert' : 'alerts'); ?>

          </span>
        </div>
        <p class="sensei-alert__body">
          Immediate action required. Coordinate with response teams to resolve the active incident workflow.
        </p>
        <div class="sensei-alert__actions">
          <a href="<?php echo e(route('emergency.index')); ?>" class="whs-btn-primary">
            <i class="icon-base ti ti-external-link"></i>
            Review Alerts
          </a>
          <a href="<?php echo e(route('incidents.create')); ?>" class="whs-btn-primary whs-btn-primary--ghost">
            <i class="icon-base ti ti-clipboard-plus"></i>
            Log Follow-up
          </a>
        </div>
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
  <?php endif; ?>

  <?php if($userVehicleAssignment && $userVehicleAssignment->vehicle): ?>
    <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => ['severity' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => 'info']); ?>
      <div class="sensei-alert">
        <div class="sensei-alert__header">
          <div>
            <span class="section-eyebrow">Your Assigned Vehicle</span>
            <h2><?php echo e($userVehicleAssignment->vehicle->make); ?> <?php echo e($userVehicleAssignment->vehicle->model); ?> (<?php echo e($userVehicleAssignment->vehicle->year); ?>)</h2>
          </div>
          <span class="whs-chip whs-chip--status-brand">
            <i class="icon-base ti ti-car"></i>
            <?php echo e($userVehicleAssignment->vehicle->registration_number); ?>

          </span>
        </div>
        <p class="sensei-alert__body">
          Daily pre-trip inspection required before operation. Current odometer: <?php echo e(number_format($userVehicleAssignment->vehicle->odometer_reading ?? 0)); ?> km.
          <?php if($userVehicleAssignment->vehicle->inspection_due_date && $userVehicleAssignment->vehicle->isInspectionDue()): ?>
            <strong class="text-danger">Inspection due: <?php echo e($userVehicleAssignment->vehicle->inspection_due_date->format('d/m/Y')); ?></strong>
          <?php endif; ?>
        </p>
        <div class="sensei-alert__actions">
          <a href="<?php echo e(route('driver.vehicle-inspections.create')); ?>" class="whs-btn-primary">
            <i class="icon-base ti ti-clipboard-check"></i>
            Start Daily Inspection
          </a>
          <a href="<?php echo e(route('vehicles.show', $userVehicleAssignment->vehicle)); ?>" class="whs-btn-primary whs-btn-primary--ghost">
            <i class="icon-base ti ti-eye"></i>
            Vehicle Details
          </a>
        </div>
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
  <?php elseif($userVehicleAssignment): ?>
    <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => ['severity' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => 'info']); ?>
      <div class="sensei-alert">
        <div class="sensei-alert__header">
          <div>
            <span class="section-eyebrow">Your Assigned Vehicle</span>
            <h2>Vehicle record unavailable</h2>
          </div>
        </div>
        <p class="sensei-alert__body">The vehicle linked to your assignment is no longer available. Please contact your fleet administrator.</p>
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
  <?php endif; ?>

  <div class="whs-main">
    <div class="sensei-panel-grid">
      <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="whs-section-heading">
          <div>
            <h2>Incident Trend</h2>
            <p>Six-month trend of recorded incidents across the organization.</p>
          </div>
        </div>
        <div id="incidentTrendChart" class="sensei-chart"></div>
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

      <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="whs-section-heading">
          <div>
            <h2>Risk Distribution</h2>
            <p>Breakdown of current risk ratings across operational areas.</p>
          </div>
        </div>
        <div id="riskDonutChart" class="sensei-chart sensei-chart--centered"></div>
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
    </div>

    <div class="sensei-panel-grid">
      <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="whs-section-heading">
          <div>
            <h2>Recent Incidents</h2>
            <p>Latest activity logged by branch teams.</p>
          </div>
          <a href="<?php echo e(route('incidents.index')); ?>" class="whs-btn-primary whs-btn-primary--ghost">
            <i class="icon-base ti ti-external-link"></i>
            View All
          </a>
        </div>
        <div class="sensei-table-wrapper">
          <table class="sensei-table">
            <thead>
              <tr>
                <th scope="col">Type</th>
                <th scope="col">Severity</th>
                <th scope="col">Date</th>
                <th scope="col">Location</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $recentIncidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td>
                    <a href="<?php echo e(route('incidents.show', $incident)); ?>" class="sensei-link">
                      <?php echo e(ucwords(str_replace('_', ' ', $incident->incident_type))); ?>

                    </a>
                  </td>
                  <td>
                    <?php switch($incident->severity):
                      case ('critical'): ?>
                        <span class="whs-chip whs-chip--status-critical">Critical</span>
                        <?php break; ?>
                      <?php case ('high'): ?>
                        <span class="whs-chip whs-chip--severity-high">High</span>
                        <?php break; ?>
                      <?php case ('medium'): ?>
                        <span class="whs-chip whs-chip--severity-medium">Medium</span>
                        <?php break; ?>
                      <?php case ('low'): ?>
                        <span class="whs-chip whs-chip--severity-low">Low</span>
                        <?php break; ?>
                    <?php endswitch; ?>
                  </td>
                  <td><?php echo e($incident->incident_datetime->format('d/m/Y')); ?></td>
                  <td><?php echo e($incident->location ?? 'Not specified'); ?></td>
                  <td>
                    <?php switch($incident->status):
                      case ('open'): ?>
                        <span class="whs-chip whs-chip--status-critical">Open</span>
                        <?php break; ?>
                      <?php case ('investigating'): ?>
                        <span class="whs-chip whs-chip--status-warning">Investigating</span>
                        <?php break; ?>
                      <?php case ('resolved'): ?>
                        <span class="whs-chip whs-chip--status-active">Resolved</span>
                        <?php break; ?>
                      <?php case ('closed'): ?>
                        <span class="whs-chip whs-chip--status">Closed</span>
                        <?php break; ?>
                    <?php endswitch; ?>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="5">
                    <div class="whs-empty">
                      <div class="whs-empty__icon">
                        <i class="icon-base ti ti-shield-check"></i>
                      </div>
                      <h3>No recent incidents</h3>
                      <p>All operations are running smoothly.</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
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

      <?php if (isset($component)) { $__componentOriginale5901ded5df0437468c08b7cbbe1dd22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5901ded5df0437468c08b7cbbe1dd22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whs.card','data' => ['class' => 'sensei-panel--actions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whs.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'sensei-panel--actions']); ?>
        <div class="whs-section-heading">
          <div>
            <h2>Quick Actions</h2>
            <p>Rapid actions for your safety operations team.</p>
          </div>
        </div>

        <div class="sensei-action-list">
          <a href="<?php echo e(route('incidents.create')); ?>" class="sensei-action sensei-action--danger">
            <span class="sensei-action__label">
              <i class="icon-base ti ti-plus"></i>
              Report Incident
            </span>
            <span class="sensei-action__meta">Log a new incident record</span>
          </a>

          <?php if($isEmployee): ?>
            <a href="<?php echo e(route('driver.vehicle-inspections.create')); ?>" class="sensei-action sensei-action--info">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-clipboard-check"></i>
                Start Vehicle Inspection
              </span>
              <span class="sensei-action__meta">Complete your daily checklist</span>
            </a>
          <?php else: ?>
            <a href="<?php echo e(route('risk.create')); ?>" class="sensei-action sensei-action--warning">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-shield"></i>
                Create Risk Assessment
              </span>
              <span class="sensei-action__meta">Update current risk register</span>
            </a>
            <a href="<?php echo e(route('emergency.create')); ?>" class="sensei-action sensei-action--danger">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-alarm"></i>
                Trigger Emergency Alert
              </span>
              <span class="sensei-action__meta">Notify response teams instantly</span>
            </a>
            <a href="<?php echo e(route('inspections.create')); ?>" class="sensei-action sensei-action--info">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-clipboard"></i>
                New Inspection
              </span>
              <span class="sensei-action__meta">Schedule a site walkthrough</span>
            </a>
            <a href="<?php echo e(route('vehicles.index')); ?>" class="sensei-action sensei-action--success">
              <span class="sensei-action__label">
                <i class="icon-base ti ti-car"></i>
                Manage Vehicles
              </span>
              <span class="sensei-action__meta">Monitor fleet readiness</span>
            </a>
          <?php endif; ?>
        </div>

        <span class="section-eyebrow">System Status</span>
        <ul class="sensei-status-list">
          <li class="sensei-status-item">
            <span>Emergency Alerts</span>
            <span class="sensei-badge <?php echo e($activeAlerts > 0 ? 'sensei-badge--danger' : 'sensei-badge--success'); ?>">
              <?php echo e($activeAlerts > 0 ? $activeAlerts . ' active' : 'All clear'); ?>

            </span>
          </li>
          <li class="sensei-status-item">
            <span>Open Incidents</span>
            <span class="sensei-badge <?php echo e($stats['open_incidents'] > 0 ? 'sensei-badge--warning' : 'sensei-badge--success'); ?>">
              <?php echo e($stats['open_incidents']); ?>

            </span>
          </li>
          <li class="sensei-status-item">
            <span>High Risks</span>
            <span class="sensei-badge <?php echo e($stats['high_risk_assessments'] > 0 ? 'sensei-badge--warning' : 'sensei-badge--success'); ?>">
              <?php echo e($stats['high_risk_assessments']); ?>

            </span>
          </li>
          <li class="sensei-status-item">
            <span>Overdue Inspections</span>
            <span class="sensei-badge <?php echo e($stats['inspections_overdue'] > 0 ? 'sensei-badge--danger' : 'sensei-badge--success'); ?>">
              <?php echo e($stats['inspections_overdue']); ?>

            </span>
          </li>
        </ul>
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
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/layoutMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\WHS5\resources\views/content/dashboard/dashboards-analytics.blade.php ENDPATH**/ ?>