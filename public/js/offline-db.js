/**
 * Rotech WHS Offline Database - IndexedDB with Dexie.js
 *
 * Purpose: Offline-first data storage for field workers
 * Features:
 * - Store incidents, risk assessments, journeys, inspections offline
 * - Auto-sync when connection restored
 * - Conflict resolution with server
 * - Photo storage with Base64 encoding
 *
 * Database Schema:
 * - incidents: Offline incident reports
 * - riskAssessments: Offline risk assessments
 * - journeys: Offline journey records
 * - inspections: Offline inspection records
 * - syncQueue: Pending changes to sync
 * - photos: Base64-encoded photos for offline access
 */

// Import Dexie from CDN (add to layout)
// <script src="https://unpkg.com/dexie@3.2.4/dist/dexie.js"></script>

const OfflineDB = (function() {
  'use strict';

  // Database instance
  let db = null;

  // Ready promise to track initialization
  let readyPromise = null;
  let isReady = false;

  // Database version
  const DB_VERSION = 2; // Incremented for syncConflicts table

  /**
   * Initialize IndexedDB with Dexie
   */
  function init() {
    if (readyPromise) {
      return readyPromise; // Already initializing or initialized
    }

    console.log('[OfflineDB] Initializing...');

    // Create Dexie database
    db = new Dexie('RotechWHS_OfflineDB');

    // Define database schema (version 1)
    db.version(DB_VERSION).stores({
      // Incidents table
      incidents: '++localId, id, type, severity, status, incident_datetime, user_id, branch_id, created_at, synced',

      // Risk Assessments table
      riskAssessments: '++localId, id, title, status, risk_score, likelihood, consequence, user_id, branch_id, created_at, synced',

      // Journeys table
      journeys: '++localId, id, destination, status, planned_departure, user_id, branch_id, created_at, synced',

      // Inspections table
      inspections: '++localId, id, vehicle_id, inspection_type, status, inspection_date, user_id, branch_id, created_at, synced',

      // Sync Queue (pending changes to sync)
      syncQueue: '++id, entity_type, entity_id, operation, data, created_at, attempts, last_attempt',

      // Sync Conflicts (optimistic locking conflicts)
      syncConflicts: '++id, entity_type, entity_id, operation, client_data, client_version, server_data, server_version, detected_at, resolved',

      // Photos (Base64-encoded for offline access)
      photos: '++id, entity_type, entity_id, file_name, base64_data, mime_type, file_size, created_at, synced',

      // Cached API responses
      apiCache: 'key, data, timestamp, expires_at',

      // User preferences
      preferences: 'key, value'
    });

    // Open database
    readyPromise = db.open()
      .then(() => {
        console.log('[OfflineDB] Database opened successfully');
        isReady = true;
        return db;
      })
      .catch((error) => {
        console.error('[OfflineDB] Failed to open database:', error);
        readyPromise = null; // Reset so init can be retried
        throw error;
      });

    return readyPromise;
  }

  /**
   * Wait for database to be ready
   * @returns {Promise} Resolves when database is ready
   */
  async function waitForReady() {
    if (isReady) {
      return Promise.resolve();
    }

    if (!readyPromise) {
      return init();
    }

    return readyPromise;
  }

  /**
   * Add incident to offline database
   * @param {Object} incidentData - Incident data object
   * @returns {Promise<number>} Local ID of created incident
   */
  async function addIncident(incidentData) {
    console.log('[OfflineDB] Adding incident:', incidentData);

    // Add timestamp and sync status
    const incident = {
      ...incidentData,
      created_at: new Date().toISOString(),
      synced: false,
      localId: Date.now() // Temporary local ID
    };

    // Add to incidents table
    const localId = await db.incidents.add(incident);

    // Add to sync queue
    await addToSyncQueue('incident', localId, 'CREATE', incident);

    console.log('[OfflineDB] Incident added with localId:', localId);
    return localId;
  }

  /**
   * Update incident in offline database
   * @param {number} localId - Local incident ID
   * @param {Object} updates - Updates to apply
   * @returns {Promise<number>} Number of rows updated
   */
  async function updateIncident(localId, updates) {
    console.log('[OfflineDB] Updating incident:', localId, updates);

    // Update incident
    const count = await db.incidents.update(localId, {
      ...updates,
      synced: false
    });

    // Add to sync queue
    await addToSyncQueue('incident', localId, 'UPDATE', updates);

    console.log('[OfflineDB] Incident updated, rows affected:', count);
    return count;
  }

  /**
   * Get all incidents from offline database
   * @param {Object} filters - Optional filters
   * @returns {Promise<Array>} Array of incidents
   */
  async function getIncidents(filters = {}) {
    console.log('[OfflineDB] Getting incidents with filters:', filters);

    let query = db.incidents.toCollection();

    // Apply filters
    if (filters.severity) {
      query = query.filter(incident => incident.severity === filters.severity);
    }
    if (filters.status) {
      query = query.filter(incident => incident.status === filters.status);
    }
    if (filters.type) {
      query = query.filter(incident => incident.type === filters.type);
    }

    // Get results
    const incidents = await query.reverse().toArray();
    console.log('[OfflineDB] Found incidents:', incidents.length);
    return incidents;
  }

  /**
   * Get single incident by local ID
   * @param {number} localId - Local incident ID
   * @returns {Promise<Object>} Incident object
   */
  async function getIncident(localId) {
    console.log('[OfflineDB] Getting incident:', localId);
    return await db.incidents.get(localId);
  }

  /**
   * Delete incident from offline database
   * @param {number} localId - Local incident ID
   * @returns {Promise<void>}
   */
  async function deleteIncident(localId) {
    console.log('[OfflineDB] Deleting incident:', localId);

    // Get incident to check if it has server ID
    const incident = await db.incidents.get(localId);

    if (incident.id) {
      // Has server ID, add to sync queue for deletion
      await addToSyncQueue('incident', localId, 'DELETE', { id: incident.id });
    } else {
      // No server ID, remove from sync queue
      await db.syncQueue
        .filter(item => item.entity_type === 'incident' && item.entity_id === localId)
        .delete();
    }

    // Delete from database
    await db.incidents.delete(localId);
    console.log('[OfflineDB] Incident deleted');
  }

  /**
   * Add photo to offline database
   * @param {Object} photoData - Photo data with Base64 encoding
   * @returns {Promise<number>} Photo ID
   */
  async function addPhoto(photoData) {
    console.log('[OfflineDB] Adding photo:', photoData.file_name);

    const photo = {
      ...photoData,
      created_at: new Date().toISOString(),
      synced: false
    };

    const photoId = await db.photos.add(photo);
    console.log('[OfflineDB] Photo added with ID:', photoId);
    return photoId;
  }

  /**
   * Get photos for entity
   * @param {string} entityType - Entity type (incident, risk_assessment, etc)
   * @param {number} entityId - Entity ID
   * @returns {Promise<Array>} Array of photos
   */
  async function getPhotos(entityType, entityId) {
    console.log('[OfflineDB] Getting photos for:', entityType, entityId);
    return await db.photos
      .filter(photo => photo.entity_type === entityType && photo.entity_id === entityId)
      .toArray();
  }

  /**
   * Add item to sync queue
   * @param {string} entityType - Entity type (incident, risk_assessment, etc)
   * @param {number} entityId - Entity ID
   * @param {string} operation - Operation (CREATE, UPDATE, DELETE)
   * @param {Object} data - Data to sync
   * @returns {Promise<number>} Queue ID
   */
  async function addToSyncQueue(entityType, entityId, operation, data) {
    console.log('[OfflineDB] Adding to sync queue:', entityType, operation);

    const queueItem = {
      entity_type: entityType,
      entity_id: entityId,
      operation: operation,
      data: JSON.stringify(data),
      created_at: new Date().toISOString(),
      attempts: 0,
      last_attempt: null
    };

    const queueId = await db.syncQueue.add(queueItem);
    console.log('[OfflineDB] Added to sync queue with ID:', queueId);
    return queueId;
  }

  /**
   * Get all pending sync items
   * @returns {Promise<Array>} Array of sync queue items
   */
  async function getSyncQueue() {
    await waitForReady();
    console.log('[OfflineDB] Getting sync queue');
    return await db.syncQueue.toArray();
  }

  /**
   * Remove item from sync queue
   * @param {number} queueId - Queue item ID
   * @returns {Promise<void>}
   */
  async function removeSyncQueueItem(queueId) {
    console.log('[OfflineDB] Removing sync queue item:', queueId);
    await db.syncQueue.delete(queueId);
  }

  /**
   * Update sync queue item attempts
   * @param {number} queueId - Queue item ID
   * @returns {Promise<void>}
   */
  async function incrementSyncAttempts(queueId) {
    const item = await db.syncQueue.get(queueId);
    if (item) {
      await db.syncQueue.update(queueId, {
        attempts: item.attempts + 1,
        last_attempt: new Date().toISOString()
      });
    }
  }

  /**
   * Mark entity as synced
   * @param {string} entityType - Entity type
   * @param {number} localId - Local entity ID
   * @param {string} serverId - Server entity ID
   * @returns {Promise<void>}
   */
  async function markAsSynced(entityType, localId, serverId) {
    console.log('[OfflineDB] Marking as synced:', entityType, localId, serverId);

    const table = {
      'incident': db.incidents,
      'risk_assessment': db.riskAssessments,
      'journey': db.journeys,
      'inspection': db.inspections
    }[entityType];

    if (table) {
      await table.update(localId, {
        id: serverId,
        synced: true
      });
    }
  }

  /**
   * Cache API response
   * @param {string} key - Cache key (URL)
   * @param {Object} data - Response data
   * @param {number} ttl - Time to live in seconds
   * @returns {Promise<void>}
   */
  async function cacheApiResponse(key, data, ttl = 300) {
    console.log('[OfflineDB] Caching API response:', key);

    const cacheItem = {
      key: key,
      data: JSON.stringify(data),
      timestamp: Date.now(),
      expires_at: Date.now() + (ttl * 1000)
    };

    await db.apiCache.put(cacheItem);
  }

  /**
   * Get cached API response
   * @param {string} key - Cache key (URL)
   * @returns {Promise<Object|null>} Cached data or null if expired
   */
  async function getCachedApiResponse(key) {
    console.log('[OfflineDB] Getting cached API response:', key);

    const cacheItem = await db.apiCache.get(key);

    if (!cacheItem) {
      return null;
    }

    // Check if expired
    if (Date.now() > cacheItem.expires_at) {
      console.log('[OfflineDB] Cache expired, removing:', key);
      await db.apiCache.delete(key);
      return null;
    }

    return JSON.parse(cacheItem.data);
  }

  /**
   * Clear expired cache items
   * @returns {Promise<number>} Number of items deleted
   */
  async function clearExpiredCache() {
    console.log('[OfflineDB] Clearing expired cache');

    const now = Date.now();
    const count = await db.apiCache
      .where('expires_at')
      .below(now)
      .delete();

    console.log('[OfflineDB] Cleared expired cache items:', count);
    return count;
  }

  /**
   * Get database statistics
   * @returns {Promise<Object>} Database statistics
   */
  async function getStats() {
    await waitForReady();
    const stats = {
      incidents: await db.incidents.count(),
      incidentsUnsynced: await db.incidents.where('synced').equals(false).count(),
      riskAssessments: await db.riskAssessments.count(),
      journeys: await db.journeys.count(),
      inspections: await db.inspections.count(),
      syncQueue: await db.syncQueue.count(),
      photos: await db.photos.count(),
      photosUnsynced: await db.photos.where('synced').equals(false).count(),
      apiCache: await db.apiCache.count()
    };

    console.log('[OfflineDB] Database statistics:', stats);
    return stats;
  }

  /**
   * Clear all data (for testing/reset)
   * @returns {Promise<void>}
   */
  async function clearAll() {
    console.warn('[OfflineDB] Clearing all data!');

    await db.incidents.clear();
    await db.riskAssessments.clear();
    await db.journeys.clear();
    await db.inspections.clear();
    await db.syncQueue.clear();
    await db.photos.clear();
    await db.apiCache.clear();

    console.log('[OfflineDB] All data cleared');
  }

  /**
   * Export data for backup
   * @returns {Promise<Object>} Exported data
   */
  async function exportData() {
    console.log('[OfflineDB] Exporting data');

    const data = {
      incidents: await db.incidents.toArray(),
      riskAssessments: await db.riskAssessments.toArray(),
      journeys: await db.journeys.toArray(),
      inspections: await db.inspections.toArray(),
      syncQueue: await db.syncQueue.toArray(),
      photos: await db.photos.toArray(),
      exported_at: new Date().toISOString()
    };

    console.log('[OfflineDB] Data exported');
    return data;
  }

  // Public API
  return {
    init,
    waitForReady,

    // Incidents
    addIncident,
    updateIncident,
    getIncidents,
    getIncident,
    deleteIncident,

    // Photos
    addPhoto,
    getPhotos,

    // Sync Queue
    addToSyncQueue,
    getSyncQueue,
    removeSyncQueueItem,
    incrementSyncAttempts,
    markAsSynced,

    // API Cache
    cacheApiResponse,
    getCachedApiResponse,
    clearExpiredCache,

    // Utilities
    getStats,
    clearAll,
    exportData,

    // Direct database access (for advanced use)
    get db() { return db; },
    get isReady() { return isReady; }
  };
})();

// Initialize database when script loads
if (typeof Dexie !== 'undefined') {
  OfflineDB.init()
    .then(() => {
      console.log('[OfflineDB] Ready');

      // Clean up expired cache on init
      OfflineDB.clearExpiredCache();
    })
    .catch((error) => {
      console.error('[OfflineDB] Initialization failed:', error);
    });
} else {
  console.error('[OfflineDB] Dexie.js not loaded! Add to layout: <script src="https://unpkg.com/dexie@3.2.4/dist/dexie.js"></script>');
}
