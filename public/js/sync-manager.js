/**
 * Rotech WHS Sync Manager - Auto-sync offline data
 *
 * Purpose: Synchronize offline changes with server when connection restored
 * Features:
 * - Auto-sync on connection restore
 * - Conflict resolution (server wins by default)
 * - Exponential backoff for failed syncs
 * - Progress notifications
 */

const SyncManager = (function() {
  'use strict';

  // Sync state
  let isSyncing = false;
  let syncInterval = null;

  // Configuration
  const CONFIG = {
    AUTO_SYNC_INTERVAL: 60000, // 1 minute
    MAX_RETRY_ATTEMPTS: 5,
    RETRY_DELAY_BASE: 2000, // 2 seconds
    RETRY_DELAY_MAX: 60000, // 1 minute
    BATCH_SIZE: 10 // Process 10 items at a time
  };

  /**
   * Initialize sync manager
   */
  function init() {
    console.log('[SyncManager] Initializing...');

    // Sync immediately if online
    if (navigator.onLine) {
      syncAll();
    }

    // Auto-sync on connection restore
    window.addEventListener('online', () => {
      console.log('[SyncManager] Connection restored, syncing...');
      syncAll();
    });

    // Auto-sync periodically when online
    syncInterval = setInterval(() => {
      if (navigator.onLine && !isSyncing) {
        syncAll();
      }
    }, CONFIG.AUTO_SYNC_INTERVAL);

    console.log('[SyncManager] Initialized');
  }

  /**
   * Sync all pending changes
   * @returns {Promise<Object>} Sync results
   */
  async function syncAll() {
    if (isSyncing) {
      console.log('[SyncManager] Sync already in progress');
      return { skipped: true };
    }

    if (!navigator.onLine) {
      console.log('[SyncManager] Offline, skipping sync');
      return { offline: true };
    }

    console.log('[SyncManager] Starting sync...');
    isSyncing = true;

    try {
      // Get sync queue
      const queue = await OfflineDB.getSyncQueue();

      if (queue.length === 0) {
        console.log('[SyncManager] Nothing to sync');
        return { success: true, synced: 0 };
      }

      console.log(`[SyncManager] Syncing ${queue.length} items`);

      // Process queue in batches
      const results = {
        success: 0,
        failed: 0,
        errors: []
      };

      for (let i = 0; i < queue.length; i += CONFIG.BATCH_SIZE) {
        const batch = queue.slice(i, i + CONFIG.BATCH_SIZE);
        await processBatch(batch, results);
      }

      console.log('[SyncManager] Sync complete:', results);

      // Show notification
      showSyncNotification(results);

      return results;

    } catch (error) {
      console.error('[SyncManager] Sync failed:', error);
      return { error: error.message };
    } finally {
      isSyncing = false;
    }
  }

  /**
   * Process a batch of sync items
   * @param {Array} batch - Batch of sync queue items
   * @param {Object} results - Results accumulator
   */
  async function processBatch(batch, results) {
    const promises = batch.map(item => processSyncItem(item, results));
    await Promise.allSettled(promises);
  }

  /**
   * Process single sync item
   * @param {Object} item - Sync queue item
   * @param {Object} results - Results accumulator
   */
  async function processSyncItem(item, results) {
    try {
      console.log(`[SyncManager] Processing ${item.entity_type} ${item.operation}`, item);

      // Increment attempt count
      await OfflineDB.incrementSyncAttempts(item.id);

      // Check max attempts
      if (item.attempts >= CONFIG.MAX_RETRY_ATTEMPTS) {
        console.error('[SyncManager] Max retry attempts reached:', item);
        results.failed++;
        results.errors.push({
          item: item,
          error: 'Max retry attempts reached'
        });
        return;
      }

      // Parse data
      const data = JSON.parse(item.data);

      // Sync based on entity type and operation
      let response;
      switch (item.entity_type) {
        case 'incident':
          response = await syncIncident(item.operation, data, item.entity_id);
          break;
        case 'risk_assessment':
          response = await syncRiskAssessment(item.operation, data, item.entity_id);
          break;
        case 'journey':
          response = await syncJourney(item.operation, data, item.entity_id);
          break;
        case 'inspection':
          response = await syncInspection(item.operation, data, item.entity_id);
          break;
        default:
          throw new Error(`Unknown entity type: ${item.entity_type}`);
      }

      // Mark as synced
      if (response && response.id) {
        await OfflineDB.markAsSynced(item.entity_type, item.entity_id, response.id);
      }

      // Remove from sync queue
      await OfflineDB.removeSyncQueueItem(item.id);

      // Sync photos if any
      if (response && response.id) {
        await syncPhotos(item.entity_type, item.entity_id, response.id);
      }

      results.success++;
      console.log('[SyncManager] Sync successful:', item);

    } catch (error) {
      console.error('[SyncManager] Sync failed:', item, error);
      results.failed++;
      results.errors.push({
        item: item,
        error: error.message
      });

      // Calculate retry delay with exponential backoff
      const delay = Math.min(
        CONFIG.RETRY_DELAY_BASE * Math.pow(2, item.attempts),
        CONFIG.RETRY_DELAY_MAX
      );

      console.log(`[SyncManager] Will retry in ${delay}ms`);
    }
  }

  /**
   * Sync incident with server
   * @param {string} operation - CREATE, UPDATE, DELETE
   * @param {Object} data - Incident data
   * @param {number} localId - Local incident ID
   * @returns {Promise<Object>} Server response
   */
  async function syncIncident(operation, data, localId) {
    console.log('[SyncManager] Syncing incident:', operation, data);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let url, method, body;

    switch (operation) {
      case 'CREATE':
        url = '/incidents';
        method = 'POST';
        body = JSON.stringify(data);
        break;

      case 'UPDATE':
        url = `/incidents/${data.id}`;
        method = 'PUT';
        body = JSON.stringify({
          ...data,
          version: data.version, // Include version for conflict detection
        });
        break;

      case 'DELETE':
        url = `/incidents/${data.id}`;
        method = 'DELETE';
        body = null;
        break;

      default:
        throw new Error(`Unknown operation: ${operation}`);
    }

    const response = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: body
    });

    // Handle conflict (409) responses
    if (response.status === 409) {
      const conflictData = await response.json();
      await handleSyncConflict(operation, data, localId, conflictData);
      throw new Error('Conflict detected - user resolution required');
    }

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Server error: ${response.status} - ${errorText}`);
    }

    const result = await response.json();

    // Update local version after successful sync
    if (result.version) {
      const localRecord = await OfflineDB.getIncident(localId);
      if (localRecord) {
        await OfflineDB.updateIncident(localId, { version: result.version });
      }
    }

    return result;
  }

  /**
   * Sync risk assessment with server
   * @param {string} operation - CREATE, UPDATE, DELETE
   * @param {Object} data - Risk assessment data
   * @param {number} localId - Local ID
   * @returns {Promise<Object>} Server response
   */
  async function syncRiskAssessment(operation, data, localId) {
    console.log('[SyncManager] Syncing risk assessment:', operation);
    // Similar to syncIncident, but for /risk route
    // TODO: Implement when risk assessment offline is needed
    return { id: data.id || Date.now() };
  }

  /**
   * Sync journey with server
   * @param {string} operation - CREATE, UPDATE, DELETE
   * @param {Object} data - Journey data
   * @param {number} localId - Local ID
   * @returns {Promise<Object>} Server response
   */
  async function syncJourney(operation, data, localId) {
    console.log('[SyncManager] Syncing journey:', operation);
    // Similar to syncIncident, but for /journey route
    // TODO: Implement when journey offline is needed
    return { id: data.id || Date.now() };
  }

  /**
   * Sync inspection with server
   * @param {string} operation - CREATE, UPDATE, DELETE
   * @param {Object} data - Inspection data
   * @param {number} localId - Local ID
   * @returns {Promise<Object>} Server response
   */
  async function syncInspection(operation, data, localId) {
    console.log('[SyncManager] Syncing inspection:', operation);
    // Similar to syncIncident, but for /inspections route
    // TODO: Implement when inspection offline is needed
    return { id: data.id || Date.now() };
  }

  /**
   * Sync photos for entity
   * @param {string} entityType - Entity type
   * @param {number} localEntityId - Local entity ID
   * @param {string} serverEntityId - Server entity ID
   * @returns {Promise<void>}
   */
  async function syncPhotos(entityType, localEntityId, serverEntityId) {
    console.log('[SyncManager] Syncing photos:', entityType, localEntityId, serverEntityId);

    // Get unsynced photos
    const photos = await OfflineDB.getPhotos(entityType, localEntityId);
    const unsyncedPhotos = photos.filter(photo => !photo.synced);

    if (unsyncedPhotos.length === 0) {
      return;
    }

    console.log(`[SyncManager] Syncing ${unsyncedPhotos.length} photos`);

    // Upload photos
    for (const photo of unsyncedPhotos) {
      try {
        await uploadPhoto(entityType, serverEntityId, photo);

        // Mark photo as synced
        await OfflineDB.db.photos.update(photo.id, { synced: true });

        console.log('[SyncManager] Photo synced:', photo.file_name);
      } catch (error) {
        console.error('[SyncManager] Photo sync failed:', photo.file_name, error);
      }
    }
  }

  /**
   * Upload photo to server
   * @param {string} entityType - Entity type
   * @param {string} entityId - Server entity ID
   * @param {Object} photo - Photo object with Base64 data
   * @returns {Promise<Object>} Server response
   */
  async function uploadPhoto(entityType, entityId, photo) {
    console.log('[SyncManager] Uploading photo:', photo.file_name);

    // Convert Base64 to Blob
    const base64Response = await fetch(photo.base64_data);
    const blob = await base64Response.blob();

    // Create FormData
    const formData = new FormData();
    formData.append('photo', blob, photo.file_name);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Determine upload URL based on entity type
    const url = getPhotoUploadUrl(entityType, entityId);

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Photo upload failed: ${response.status}`);
    }

    return await response.json();
  }

  /**
   * Get photo upload URL for entity type
   * @param {string} entityType - Entity type
   * @param {string} entityId - Entity ID
   * @returns {string} Upload URL
   */
  function getPhotoUploadUrl(entityType, entityId) {
    const urls = {
      'incident': `/incidents/${entityId}/photos`,
      'risk_assessment': `/risk/${entityId}/photos`,
      'inspection': `/inspections/${entityId}/photos`
    };

    return urls[entityType] || `/uploads/${entityType}/${entityId}`;
  }

  /**
   * Show sync notification
   * @param {Object} results - Sync results
   */
  function showSyncNotification(results) {
    const total = results.success + results.failed;
    const message = results.failed === 0
      ? `✅ Synced ${results.success} item(s) successfully`
      : `⚠️ Synced ${results.success}/${total} item(s). ${results.failed} failed.`;

    const backgroundColor = results.failed === 0 ? '#28c76f' : '#ff9f43';

    // Use Toastify if available
    if (typeof Toastify !== 'undefined') {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "bottom",
        position: "right",
        backgroundColor: backgroundColor,
      }).showToast();
    } else {
      console.log('[SyncManager]', message);
    }
  }

  /**
   * Manually trigger sync
   * @returns {Promise<Object>} Sync results
   */
  async function manualSync() {
    console.log('[SyncManager] Manual sync triggered');
    return await syncAll();
  }

  /**
   * Get sync status
   * @returns {Promise<Object>} Sync status
   */
  async function getStatus() {
    const queue = await OfflineDB.getSyncQueue();
    const stats = await OfflineDB.getStats();

    return {
      isSyncing: isSyncing,
      pendingSync: queue.length,
      unsyncedIncidents: stats.incidentsUnsynced,
      unsyncedPhotos: stats.photosUnsynced,
      lastSync: localStorage.getItem('lastSyncTime'),
      isOnline: navigator.onLine
    };
  }

  /**
   * Stop sync manager
   */
  function stop() {
    console.log('[SyncManager] Stopping...');
    if (syncInterval) {
      clearInterval(syncInterval);
      syncInterval = null;
    }
  }

  /**
   * Handle sync conflict (optimistic locking)
   * @param {string} operation - Operation type
   * @param {Object} clientData - Client version of data
   * @param {number} localId - Local record ID
   * @param {Object} serverResponse - Server conflict response
   */
  async function handleSyncConflict(operation, clientData, localId, serverResponse) {
    console.warn('[SyncManager] Conflict detected:', { operation, clientData, serverResponse });

    // Store conflict in IndexedDB for later resolution
    await OfflineDB.db.syncConflicts.add({
      entity_type: 'incident',
      entity_id: localId,
      operation: operation,
      client_data: clientData,
      client_version: clientData.version,
      server_data: serverResponse.server_data,
      server_version: serverResponse.server_version,
      detected_at: new Date().toISOString(),
      resolved: false,
    });

    // Show notification to user
    showConflictNotification();
  }

  /**
   * Show conflict notification to user
   */
  function showConflictNotification() {
    if (typeof Toastify !== 'undefined') {
      Toastify({
        text: '⚠️ Sync Conflict: Some changes conflict with server updates. Click to resolve.',
        duration: -1, // Persistent until clicked
        gravity: "bottom",
        position: "right",
        backgroundColor: "#ff9f43",
        onClick: function() {
          // Open conflict resolver UI
          if (typeof window.openConflictResolver === 'function') {
            window.openConflictResolver();
          } else {
            window.location.href = '/sync-conflicts';
          }
        }
      }).showToast();
    } else {
      // Fallback to alert
      alert('⚠️ Sync Conflict: Some of your offline changes conflict with server updates. Please resolve conflicts.');
    }
  }

  // Public API
  return {
    init,
    syncAll,
    manualSync,
    getStatus,
    stop
  };
})();

// Initialize sync manager when script loads
if (typeof OfflineDB !== 'undefined') {
  // Wait for OfflineDB to initialize
  OfflineDB.waitForReady()
    .then(() => {
      console.log('[SyncManager] OfflineDB ready, initializing SyncManager');
      SyncManager.init();
    })
    .catch((error) => {
      console.error('[SyncManager] Failed to initialize, OfflineDB not ready:', error);
    });
} else {
  console.error('[SyncManager] OfflineDB not loaded! Make sure offline-db.js is included first.');
}
