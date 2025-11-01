/**
 * Rotech WHS Conflict Resolver
 *
 * Purpose: User interface for resolving PWA sync conflicts
 * Features:
 * - Display client vs server data side-by-side
 * - Allow user to choose version or merge
 * - Update IndexedDB and retry sync
 */

const ConflictResolver = (function() {
  'use strict';

  let modalElement = null;
  let currentConflict = null;

  /**
   * Initialize conflict resolver
   */
  function init() {
    console.log('[ConflictResolver] Initializing...');
    createModal();
    window.openConflictResolver = openResolver;
  }

  /**
   * Create modal HTML
   */
  function createModal() {
    const modalHTML = `
      <div class="modal fade" id="syncConflictModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header bg-warning">
              <h5 class="modal-title">
                <i class="bx bx-error"></i> Sync Conflict Resolution
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-info">
                <strong>What happened?</strong> You made changes offline while someone else modified the same record.
                Choose which version to keep, or merge the changes manually.
              </div>

              <div id="conflictDetails"></div>

              <div class="row mt-4">
                <div class="col-md-6">
                  <h6 class="text-primary">Your Changes (Local)</h6>
                  <div class="card">
                    <div class="card-body">
                      <pre id="clientData" class="mb-0" style="font-size: 0.85rem;"></pre>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <h6 class="text-success">Server Version</h6>
                  <div class="card">
                    <div class="card-body">
                      <pre id="serverData" class="mb-0" style="font-size: 0.85rem;"></pre>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="ConflictResolver.resolveKeepLocal()">
                <i class="bx bx-check"></i> Keep My Changes
              </button>
              <button type="button" class="btn btn-success" onclick="ConflictResolver.resolveKeepServer()">
                <i class="bx bx-download"></i> Use Server Version
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    modalElement = document.getElementById('syncConflictModal');
  }

  /**
   * Open resolver with conflicts
   */
  async function openResolver() {
    console.log('[ConflictResolver] Opening conflict resolver...');

    // Get unresolved conflicts
    const conflicts = await OfflineDB.db.syncConflicts
      .where('resolved').equals(false)
      .toArray();

    if (conflicts.length === 0) {
      alert('No conflicts to resolve.');
      return;
    }

    // Show first conflict
    currentConflict = conflicts[0];
    displayConflict(currentConflict);

    // Show modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
  }

  /**
   * Display conflict data
   */
  function displayConflict(conflict) {
    document.getElementById('conflictDetails').innerHTML = `
      <div class="mb-3">
        <span class="badge bg-warning">${conflict.entity_type}</span>
        <span class="badge bg-secondary">Entity ID: ${conflict.entity_id}</span>
        <span class="badge bg-info">Operation: ${conflict.operation}</span>
      </div>
      <p class="text-muted mb-3">
        Detected at: ${new Date(conflict.detected_at).toLocaleString()}
      </p>
    `;

    document.getElementById('clientData').textContent =
      JSON.stringify(conflict.client_data, null, 2);

    document.getElementById('serverData').textContent =
      JSON.stringify(conflict.server_data, null, 2);
  }

  /**
   * Resolve by keeping local changes
   */
  async function resolveKeepLocal() {
    if (!currentConflict) return;

    console.log('[ConflictResolver] Resolving: Keep local changes');

    // Force update with client data (increment version from server)
    const updatedData = {
      ...currentConflict.client_data,
      version: currentConflict.server_version, // Use server version to avoid conflict
    };

    // Update sync queue
    await OfflineDB.db.syncQueue.add({
      entity_type: currentConflict.entity_type,
      entity_id: currentConflict.entity_id,
      operation: 'UPDATE',
      data: updatedData,
      created_at: new Date().toISOString(),
      attempts: 0,
    });

    // Mark conflict as resolved
    await OfflineDB.db.syncConflicts.update(currentConflict.id, {
      resolved: true,
      resolution: 'keep_local',
      resolved_at: new Date().toISOString(),
    });

    // Trigger sync
    if (typeof SyncManager !== 'undefined') {
      SyncManager.syncAll();
    }

    closeModal();
  }

  /**
   * Resolve by using server version
   */
  async function resolveKeepServer() {
    if (!currentConflict) return;

    console.log('[ConflictResolver] Resolving: Use server version');

    // Update local data with server version
    switch (currentConflict.entity_type) {
      case 'incident':
        await OfflineDB.updateIncident(
          currentConflict.entity_id,
          currentConflict.server_data
        );
        break;
      // Add other entity types as needed
    }

    // Mark conflict as resolved
    await OfflineDB.db.syncConflicts.update(currentConflict.id, {
      resolved: true,
      resolution: 'keep_server',
      resolved_at: new Date().toISOString(),
    });

    closeModal();
  }

  /**
   * Close modal
   */
  function closeModal() {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
    currentConflict = null;
  }

  // Public API
  return {
    init,
    openResolver,
    resolveKeepLocal,
    resolveKeepServer
  };
})();

// Initialize when DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', ConflictResolver.init);
} else {
  ConflictResolver.init();
}
