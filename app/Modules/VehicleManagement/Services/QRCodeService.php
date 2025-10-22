<?php

namespace App\Modules\VehicleManagement\Services;

use App\Modules\VehicleManagement\Models\Vehicle;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for a vehicle
     */
    public function generateVehicleQRCode(Vehicle $vehicle): string
    {
        // Create vehicle profile URL
        $url = route('vehicles.show', $vehicle->id);

        // Generate QR code as SVG
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);

        // Create directory if it doesn't exist
        $directory = 'qr-codes/vehicles';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Save QR code
        $filename = "{$directory}/{$vehicle->id}.svg";
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Generate QR code with custom data
     */
    public function generateQRCode(string $data, string $filename = null): string
    {
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($data);

        $directory = 'qr-codes';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = $filename ?? uniqid() . '.svg';
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $qrCode);

        return $path;
    }

    /**
     * Delete QR code file
     */
    public function deleteQRCode(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Get QR code public URL
     */
    public function getQRCodeUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
