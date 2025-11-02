/**
 * WHS5 Image Compression Utility
 *
 * Compresses images client-side before upload to reduce upload time by 80-90%
 * - Resizes to reasonable dimensions
 * - Converts to JPEG at 85% quality
 * - Target: ~800KB per image (down from 3-8MB)
 */

class ImageCompressor {
    constructor(options = {}) {
        this.maxWidth = options.maxWidth || 1920;
        this.maxHeight = options.maxHeight || 1920;
        this.quality = options.quality || 0.85;
        this.targetSizeKB = options.targetSizeKB || 800;
        this.debug = options.debug || false;
    }

    /**
     * Compress a single file
     */
    async compressFile(file) {
        // Skip if not an image
        if (!file.type.startsWith('image/')) {
            return file;
        }

        // Skip if already small enough
        const fileSizeKB = file.size / 1024;
        if (fileSizeKB < this.targetSizeKB) {
            this.log(`File already small (${fileSizeKB.toFixed(0)}KB), skipping compression`);
            return file;
        }

        this.log(`Compressing ${file.name} (${fileSizeKB.toFixed(0)}KB)...`);

        try {
            const compressed = await this.compress(file);
            const compressedSizeKB = compressed.size / 1024;
            const reduction = ((1 - compressed.size / file.size) * 100).toFixed(0);

            this.log(`✓ Compressed to ${compressedSizeKB.toFixed(0)}KB (${reduction}% reduction)`);

            return compressed;
        } catch (error) {
            console.error('Compression failed:', error);
            return file; // Fallback to original
        }
    }

    /**
     * Core compression logic
     */
    async compress(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (e) => {
                const img = new Image();

                img.onload = () => {
                    try {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        // Calculate new dimensions
                        let { width, height } = this.calculateDimensions(img.width, img.height);

                        canvas.width = width;
                        canvas.height = height;

                        // Draw and compress
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(
                            (blob) => {
                                if (!blob) {
                                    reject(new Error('Blob creation failed'));
                                    return;
                                }

                                // Create new File object
                                const compressedFile = new File(
                                    [blob],
                                    file.name.replace(/\.\w+$/, '.jpg'),
                                    {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    }
                                );

                                resolve(compressedFile);
                            },
                            'image/jpeg',
                            this.quality
                        );
                    } catch (error) {
                        reject(error);
                    }
                };

                img.onerror = () => reject(new Error('Image load failed'));
                img.src = e.target.result;
            };

            reader.onerror = () => reject(new Error('File read failed'));
            reader.readAsDataURL(file);
        });
    }

    /**
     * Calculate dimensions maintaining aspect ratio
     */
    calculateDimensions(width, height) {
        if (width <= this.maxWidth && height <= this.maxHeight) {
            return { width, height };
        }

        const ratio = Math.min(this.maxWidth / width, this.maxHeight / height);

        return {
            width: Math.round(width * ratio),
            height: Math.round(height * ratio)
        };
    }

    /**
     * Debug logging
     */
    log(message) {
        if (this.debug) {
            console.log(`[ImageCompressor] ${message}`);
        }
    }
}

/**
 * Auto-compress file inputs on change
 */
function initImageCompression(options = {}) {
    const compressor = new ImageCompressor({
        maxWidth: options.maxWidth || 1920,
        maxHeight: options.maxHeight || 1920,
        quality: options.quality || 0.85,
        targetSizeKB: options.targetSizeKB || 800,
        debug: options.debug || false
    });

    // Find all file inputs that accept images
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');

    fileInputs.forEach(input => {
        input.addEventListener('change', async function(e) {
            const files = Array.from(e.target.files);

            if (files.length === 0) return;

            // Show compression indicator
            showCompressionIndicator(input, true);

            try {
                // Compress all files
                const compressedFiles = await Promise.all(
                    files.map(file => compressor.compressFile(file))
                );

                // Create new FileList
                const dataTransfer = new DataTransfer();
                compressedFiles.forEach(file => dataTransfer.items.add(file));

                // Replace input files
                e.target.files = dataTransfer.files;

                // Update UI
                updateFileInputLabel(input, compressedFiles);
                showCompressionIndicator(input, false);

            } catch (error) {
                console.error('Compression failed:', error);
                showCompressionIndicator(input, false);
            }
        });
    });
}

/**
 * Show/hide compression indicator
 */
function showCompressionIndicator(input, show) {
    const wrapper = input.closest('.mb-3, .col-md-6') || input.parentElement;
    if (!wrapper) return;

    let indicator = wrapper.querySelector('.compression-indicator');

    if (show) {
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'compression-indicator small text-primary mt-1';
            indicator.innerHTML = '<i class="ti ti-loader ti-spin me-1"></i>Optimizing image...';
            input.parentElement.appendChild(indicator);
        }
        indicator.style.display = 'block';
    } else {
        if (indicator) {
            indicator.style.display = 'none';
        }
    }
}

/**
 * Update file input label with compressed file info
 */
function updateFileInputLabel(input, files) {
    const wrapper = input.closest('.mb-3, .col-md-6') || input.parentElement;
    if (!wrapper) return;

    let feedback = wrapper.querySelector('.file-feedback');

    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'file-feedback small text-success mt-1';
        input.parentElement.appendChild(feedback);
    }

    const totalSizeKB = files.reduce((sum, f) => sum + f.size, 0) / 1024;
    feedback.innerHTML = `<i class="ti ti-check-circle me-1"></i>Ready (${totalSizeKB.toFixed(0)}KB)`;
    feedback.style.display = 'block';
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initImageCompression());
} else {
    initImageCompression();
}
