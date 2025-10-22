/**
 * WHS4 Offline Indicator - Network Status UI Component
 *
 * Purpose: Visual indicators for offline/online status and sync progress
 * Features:
 * - Navbar status badge
 * - Sync progress indicator
 * - Toast notifications
 * - Manual sync button
 */

const OfflineIndicator = (function() {
  'use strict';

  // UI elements
  let indicatorElement = null;
  let syncButton = null;
  let statusBadge = null;
  let progressBar = null;

  // State
  let isInitialized = false;
  let currentStatus = 'unknown';

  /**
   * Initialize offline indicator
   */
  function init() {
    console.log('[OfflineIndicator] Initializing...');

    // Create indicator elements
    createIndicatorHTML();

    // Set initial status
    updateStatus(navigator.onLine ? 'online' : 'offline');

    // Listen for online/offline events
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    // Update sync status periodically
    setInterval(updateSyncStatus, 5000);

    // Initial sync status update
    updateSyncStatus();

    isInitialized = true;
    console.log('[OfflineIndicator] Initialized');
  }

  /**
   * Create indicator HTML and inject into navbar
   */
  function createIndicatorHTML() {
    // Find navbar user dropdown area
    const navbarNav = document.querySelector('.navbar-nav');
    if (!navbarNav) {
      console.warn('[OfflineIndicator] Navbar not found');
      return;
    }

    // Create indicator container
    const container = document.createElement('li');
    container.className = 'nav-item offline-indicator-container me-3';
    container.innerHTML = `
      <div class="d-flex align-items-center">
        <!-- Status Badge -->
        <span id="offlineStatusBadge" class="badge bg-label-success me-2">
          <i class="bx bx-wifi icon-xs me-1"></i>
          <span class="status-text">Online</span>
        </span>

        <!-- Sync Button (hidden when online and synced) -->
        <button id="manualSyncButton" class="btn btn-sm btn-outline-primary d-none" title="Sync pending changes">
          <i class="bx bx-sync icon-xs"></i>
          <span class="sync-count badge bg-danger ms-1">0</span>
        </button>
      </div>

      <!-- Sync Progress Bar (shown during sync) -->
      <div id="syncProgressBar" class="progress mt-2 d-none" style="height: 3px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
             role="progressbar" style="width: 0%"></div>
      </div>
    `;

    // Insert before user dropdown
    const userDropdown = navbarNav.querySelector('.navbar-nav-right');
    if (userDropdown) {
      userDropdown.parentElement.insertBefore(container, userDropdown);
    } else {
      navbarNav.appendChild(container);
    }

    // Get references to elements
    indicatorElement = container;
    statusBadge = document.getElementById('offlineStatusBadge');
    syncButton = document.getElementById('manualSyncButton');
    progressBar = document.getElementById('syncProgressBar');

    // Attach sync button handler
    if (syncButton) {
      syncButton.addEventListener('click', handleManualSync);
    }
  }

  /**
   * Update status display
   * @param {string} status - 'online', 'offline', 'syncing'
   */
  function updateStatus(status) {
    if (!statusBadge) return;

    currentStatus = status;
    console.log('[OfflineIndicator] Status:', status);

    // Update badge appearance
    switch (status) {
      case 'online':
        statusBadge.className = 'badge bg-label-success me-2';
        statusBadge.innerHTML = '<i class="bx bx-wifi icon-xs me-1"></i><span class="status-text">Online</span>';
        break;

      case 'offline':
        statusBadge.className = 'badge bg-label-danger me-2';
        statusBadge.innerHTML = '<i class="bx bx-wifi-off icon-xs me-1"></i><span class="status-text">Offline</span>';
        break;

      case 'syncing':
        statusBadge.className = 'badge bg-label-warning me-2';
        statusBadge.innerHTML = '<i class="bx bx-sync bx-spin icon-xs me-1"></i><span class="status-text">Syncing</span>';
        break;

      case 'error':
        statusBadge.className = 'badge bg-label-danger me-2';
        statusBadge.innerHTML = '<i class="bx bx-error icon-xs me-1"></i><span class="status-text">Sync Error</span>';
        break;

      default:
        statusBadge.className = 'badge bg-label-secondary me-2';
        statusBadge.innerHTML = '<i class="bx bx-question-mark icon-xs me-1"></i><span class="status-text">Unknown</span>';
    }
  }

  /**
   * Update sync status and button visibility
   */
  async function updateSyncStatus() {
    if (typeof SyncManager === 'undefined') {
      return;
    }

    // Wait for OfflineDB to be ready
    if (typeof OfflineDB !== 'undefined' && !OfflineDB.isReady) {
      return;
    }

    try {
      const status = await SyncManager.getStatus();

      // Update sync button visibility and count
      if (syncButton) {
        const syncCount = syncButton.querySelector('.sync-count');

        if (status.pendingSync > 0) {
          // Show sync button with count
          syncButton.classList.remove('d-none');
          if (syncCount) {
            syncCount.textContent = status.pendingSync;
          }
        } else {
          // Hide sync button when nothing to sync
          syncButton.classList.add('d-none');
        }
      }

      // Update status based on sync state
      if (status.isSyncing) {
        updateStatus('syncing');
      } else if (!status.isOnline) {
        updateStatus('offline');
      } else if (status.pendingSync > 0) {
        // Online but with pending items (retry failed)
        statusBadge.className = 'badge bg-label-warning me-2';
        statusBadge.innerHTML = `<i class="bx bx-wifi icon-xs me-1"></i><span class="status-text">Online (${status.pendingSync} pending)</span>`;
      } else {
        updateStatus('online');
      }

    } catch (error) {
      console.error('[OfflineIndicator] Failed to update sync status:', error);
    }
  }

  /**
   * Handle online event
   */
  function handleOnline() {
    console.log('[OfflineIndicator] Connection restored');
    updateStatus('online');

    // Show notification
    showNotification('✅ Back Online', 'Changes will sync automatically', 'success');

    // Update sync status
    updateSyncStatus();
  }

  /**
   * Handle offline event
   */
  function handleOffline() {
    console.log('[OfflineIndicator] Connection lost');
    updateStatus('offline');

    // Show notification
    showNotification('⚠️ You\'re Offline', 'Changes will be saved locally', 'warning');
  }

  /**
   * Handle manual sync button click
   */
  async function handleManualSync() {
    console.log('[OfflineIndicator] Manual sync triggered');

    if (!navigator.onLine) {
      showNotification('⚠️ Still Offline', 'Cannot sync without internet connection', 'warning');
      return;
    }

    // Disable button during sync
    if (syncButton) {
      syncButton.disabled = true;
      syncButton.innerHTML = '<i class="bx bx-sync bx-spin icon-xs"></i>';
    }

    updateStatus('syncing');
    showProgressBar();

    try {
      // Trigger manual sync
      const results = await SyncManager.manualSync();

      if (results.success > 0) {
        showNotification(
          '✅ Sync Complete',
          `Successfully synced ${results.success} item(s)`,
          'success'
        );
      } else if (results.offline) {
        showNotification('⚠️ Offline', 'Cannot sync without internet', 'warning');
      } else if (results.failed > 0) {
        showNotification(
          '⚠️ Partial Sync',
          `Synced ${results.success}/${results.success + results.failed} items. ${results.failed} failed.`,
          'warning'
        );
      }

    } catch (error) {
      console.error('[OfflineIndicator] Sync failed:', error);
      showNotification('❌ Sync Failed', error.message, 'error');
      updateStatus('error');
    } finally {
      // Re-enable button
      if (syncButton) {
        syncButton.disabled = false;
        syncButton.innerHTML = '<i class="bx bx-sync icon-xs"></i><span class="sync-count badge bg-danger ms-1">0</span>';
      }

      hideProgressBar();
      updateSyncStatus();
    }
  }

  /**
   * Show progress bar
   */
  function showProgressBar() {
    if (!progressBar) return;

    progressBar.classList.remove('d-none');
    const bar = progressBar.querySelector('.progress-bar');
    if (bar) {
      bar.style.width = '0%';

      // Animate to 90% (100% when complete)
      setTimeout(() => {
        bar.style.width = '90%';
      }, 100);
    }
  }

  /**
   * Hide progress bar
   */
  function hideProgressBar() {
    if (!progressBar) return;

    const bar = progressBar.querySelector('.progress-bar');
    if (bar) {
      bar.style.width = '100%';
    }

    setTimeout(() => {
      progressBar.classList.add('d-none');
    }, 500);
  }

  /**
   * Show notification
   * @param {string} title - Notification title
   * @param {string} message - Notification message
   * @param {string} type - Notification type (success, warning, error)
   */
  function showNotification(title, message, type = 'info') {
    const colors = {
      success: '#28c76f',
      warning: '#ff9f43',
      error: '#ea5455',
      info: '#00cfe8'
    };

    const text = `<strong>${title}</strong><br>${message}`;

    // Use Toastify if available
    if (typeof Toastify !== 'undefined') {
      Toastify({
        text: text,
        duration: type === 'error' ? 5000 : 3000,
        gravity: "top",
        position: "right",
        backgroundColor: colors[type] || colors.info,
        escapeMarkup: false,
      }).showToast();
    } else {
      console.log(`[OfflineIndicator] ${title}: ${message}`);
    }
  }

  /**
   * Show offline form warning
   * @param {HTMLElement} formElement - Form element
   */
  function showFormWarning(formElement) {
    if (!formElement) return;

    // Check if warning already exists
    let warning = formElement.querySelector('.offline-form-warning');
    if (warning) {
      // Update visibility based on status
      warning.classList.toggle('d-none', navigator.onLine);
      return;
    }

    // Create warning element
    warning = document.createElement('div');
    warning.className = 'alert alert-warning offline-form-warning mb-3' + (navigator.onLine ? ' d-none' : '');
    warning.innerHTML = `
      <div class="d-flex align-items-center">
        <i class="bx bx-wifi-off me-2 fs-4"></i>
        <div>
          <strong>You're Offline</strong><br>
          <small>Your submission will be saved locally and synced when connection is restored.</small>
        </div>
      </div>
    `;

    // Insert at top of form
    formElement.insertBefore(warning, formElement.firstChild);

    // Update on online/offline events
    window.addEventListener('online', () => {
      warning.classList.add('d-none');
    });

    window.addEventListener('offline', () => {
      warning.classList.remove('d-none');
    });
  }

  /**
   * Get current status
   * @returns {Object} Current status
   */
  function getStatus() {
    return {
      isOnline: navigator.onLine,
      currentStatus: currentStatus,
      isInitialized: isInitialized
    };
  }

  // Public API
  return {
    init,
    updateStatus,
    showNotification,
    showFormWarning,
    getStatus
  };
})();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    OfflineIndicator.init();
  });
} else {
  OfflineIndicator.init();
}
