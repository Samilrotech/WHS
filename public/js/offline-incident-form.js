/**
 * Rotech WHS Offline Incident Form Handler
 *
 * Purpose: Handle incident creation/editing while offline
 * Features:
 * - Save incidents to IndexedDB when offline
 * - Auto-sync when connection restored
 * - Photo capture with Base64 encoding
 * - Form validation
 */

const OfflineIncidentForm = (function() {
  'use strict';

  // Form state
  let formElement = null;
  let photoInput = null;
  let capturedPhotos = [];

  /**
   * Initialize offline incident form
   * @param {string} formSelector - Form CSS selector
   */
  function init(formSelector = '#incidentForm') {
    console.log('[OfflineIncidentForm] Initializing...');

    formElement = document.querySelector(formSelector);
    if (!formElement) {
      console.warn('[OfflineIncidentForm] Form not found:', formSelector);
      return;
    }

    // Setup form submission handler
    formElement.addEventListener('submit', handleSubmit);

    // Setup photo input
    photoInput = formElement.querySelector('input[type="file"][name="photos[]"]');
    if (photoInput) {
      photoInput.addEventListener('change', handlePhotoCapture);
    }

    // Show offline indicator if offline
    updateOfflineIndicator();

    console.log('[OfflineIncidentForm] Initialized');
  }

  /**
   * Handle form submission
   * @param {Event} event - Submit event
   */
  async function handleSubmit(event) {
    event.preventDefault();
    console.log('[OfflineIncidentForm] Form submitted');

    // Get form data
    const formData = new FormData(formElement);
    const incidentData = {
      type: formData.get('type'),
      severity: formData.get('severity'),
      status: formData.get('status') || 'reported',
      incident_datetime: formData.get('incident_datetime'),
      location_specific: formData.get('location_specific'),
      location_lat: formData.get('location_lat'),
      location_lng: formData.get('location_lng'),
      description: formData.get('description'),
      immediate_actions: formData.get('immediate_actions'),
      witnesses: formData.get('witnesses'),
      user_id: formData.get('user_id') || null,
      branch_id: formData.get('branch_id') || null
    };

    // Validate required fields
    if (!validateIncidentData(incidentData)) {
      showError('Please fill in all required fields');
      return;
    }

    try {
      if (navigator.onLine) {
        // Online: Submit to server directly
        await submitToServer(incidentData);
      } else {
        // Offline: Save to IndexedDB
        await saveOffline(incidentData);
      }
    } catch (error) {
      console.error('[OfflineIncidentForm] Submission failed:', error);
      showError('Failed to submit incident: ' + error.message);
    }
  }

  /**
   * Validate incident data
   * @param {Object} data - Incident data
   * @returns {boolean} Is valid
   */
  function validateIncidentData(data) {
    const required = ['type', 'severity', 'incident_datetime', 'description'];

    for (const field of required) {
      if (!data[field]) {
        console.error('[OfflineIncidentForm] Missing required field:', field);
        return false;
      }
    }

    return true;
  }

  /**
   * Submit incident to server
   * @param {Object} data - Incident data
   * @returns {Promise<void>}
   */
  async function submitToServer(data) {
    console.log('[OfflineIncidentForm] Submitting to server...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Submit incident
    const response = await fetch('/incidents', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Server error');
    }

    const result = await response.json();
    console.log('[OfflineIncidentForm] Submitted successfully:', result);

    // Upload photos if any
    if (capturedPhotos.length > 0 && result.id) {
      await uploadPhotos(result.id);
    }

    showSuccess('Incident reported successfully');

    // Redirect to incident show page
    setTimeout(() => {
      window.location.href = `/incidents/${result.id}`;
    }, 1500);
  }

  /**
   * Save incident offline
   * @param {Object} data - Incident data
   * @returns {Promise<void>}
   */
  async function saveOffline(data) {
    console.log('[OfflineIncidentForm] Saving offline...');

    // Add to IndexedDB
    const localId = await OfflineDB.addIncident(data);

    // Save photos to IndexedDB
    for (const photo of capturedPhotos) {
      await OfflineDB.addPhoto({
        entity_type: 'incident',
        entity_id: localId,
        file_name: photo.name,
        base64_data: photo.base64,
        mime_type: photo.type,
        file_size: photo.size
      });
    }

    showSuccess('Incident saved offline. Will sync when connection restored.');

    // Reset form
    formElement.reset();
    capturedPhotos = [];

    // Redirect to incidents list
    setTimeout(() => {
      window.location.href = '/incidents';
    }, 2000);
  }

  /**
   * Handle photo capture
   * @param {Event} event - Change event
   */
  async function handlePhotoCapture(event) {
    const files = event.target.files;
    console.log('[OfflineIncidentForm] Photos selected:', files.length);

    for (let i = 0; i < files.length; i++) {
      const file = files[i];

      // Validate file size (10MB max)
      if (file.size > 10 * 1024 * 1024) {
        showError(`Photo ${file.name} is too large (max 10MB)`);
        continue;
      }

      // Validate file type
      if (!file.type.startsWith('image/')) {
        showError(`File ${file.name} is not an image`);
        continue;
      }

      // Convert to Base64 for offline storage
      const base64 = await fileToBase64(file);

      capturedPhotos.push({
        name: file.name,
        type: file.type,
        size: file.size,
        base64: base64,
        file: file
      });

      console.log('[OfflineIncidentForm] Photo captured:', file.name);
    }

    // Show preview
    showPhotoPreview();
  }

  /**
   * Convert file to Base64
   * @param {File} file - File object
   * @returns {Promise<string>} Base64 data URL
   */
  function fileToBase64(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = () => resolve(reader.result);
      reader.onerror = () => reject(new Error('Failed to read file'));

      reader.readAsDataURL(file);
    });
  }

  /**
   * Upload photos to server
   * @param {string} incidentId - Incident ID
   * @returns {Promise<void>}
   */
  async function uploadPhotos(incidentId) {
    console.log('[OfflineIncidentForm] Uploading photos...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    for (const photo of capturedPhotos) {
      const formData = new FormData();
      formData.append('photo', photo.file);

      try {
        const response = await fetch(`/incidents/${incidentId}/photos`, {
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

        console.log('[OfflineIncidentForm] Photo uploaded:', photo.name);
      } catch (error) {
        console.error('[OfflineIncidentForm] Photo upload failed:', photo.name, error);
      }
    }
  }

  /**
   * Show photo preview
   */
  function showPhotoPreview() {
    // Find or create preview container
    let previewContainer = document.getElementById('photoPreview');
    if (!previewContainer) {
      previewContainer = document.createElement('div');
      previewContainer.id = 'photoPreview';
      previewContainer.className = 'photo-preview mt-3';
      photoInput.parentElement.appendChild(previewContainer);
    }

    // Clear existing previews
    previewContainer.innerHTML = '';

    // Add photo previews
    capturedPhotos.forEach((photo, index) => {
      const previewDiv = document.createElement('div');
      previewDiv.className = 'photo-preview-item';
      previewDiv.innerHTML = `
        <img src="${photo.base64}" alt="${photo.name}" style="max-width: 100px; max-height: 100px; margin: 5px;">
        <button type="button" class="btn btn-sm btn-danger" onclick="OfflineIncidentForm.removePhoto(${index})">
          <i class="bx bx-trash"></i>
        </button>
      `;
      previewContainer.appendChild(previewDiv);
    });
  }

  /**
   * Remove photo from captured photos
   * @param {number} index - Photo index
   */
  function removePhoto(index) {
    console.log('[OfflineIncidentForm] Removing photo:', index);
    capturedPhotos.splice(index, 1);
    showPhotoPreview();
  }

  /**
   * Update offline indicator
   */
  function updateOfflineIndicator() {
    const indicator = document.getElementById('offlineIndicator');
    if (!indicator) {
      return;
    }

    if (navigator.onLine) {
      indicator.classList.add('d-none');
    } else {
      indicator.classList.remove('d-none');
      indicator.innerHTML = `
        <div class="alert alert-warning">
          <i class="bx bx-wifi-off me-2"></i>
          You're offline. Incident will be saved locally and synced when connection is restored.
        </div>
      `;
    }
  }

  /**
   * Show success message
   * @param {string} message - Success message
   */
  function showSuccess(message) {
    console.log('[OfflineIncidentForm] Success:', message);

    if (typeof Toastify !== 'undefined') {
      Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#28c76f",
      }).showToast();
    } else {
      alert(message);
    }
  }

  /**
   * Show error message
   * @param {string} message - Error message
   */
  function showError(message) {
    console.error('[OfflineIncidentForm] Error:', message);

    if (typeof Toastify !== 'undefined') {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        backgroundColor: "#ea5455",
      }).showToast();
    } else {
      alert(message);
    }
  }

  // Public API
  return {
    init,
    removePhoto
  };
})();
