import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../models/vehicle_model.dart';
import '../../inspections/screens/monthly_inspection_form_screen.dart';

/// My Vehicle screen showing assigned vehicle and inspection status
class MyVehicleScreen extends ConsumerWidget {
  const MyVehicleScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // TODO: Replace with actual API call to get user's assigned vehicle
    // For now, showing placeholder UI
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Vehicle & Inspections'),
      ),
      body: _buildNoVehicleAssigned(context),
    );
  }

  Widget _buildNoVehicleAssigned(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.directions_car_outlined,
              size: 100,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 24),
            Text(
              'No Vehicle Assigned',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 12),
            Text(
              'You don\'t have a vehicle assigned to you yet. Please contact your manager for vehicle assignment.',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildVehicleView(BuildContext context, VehicleModel vehicle) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Vehicle Info Card
          _buildVehicleInfoCard(context, vehicle),
          const SizedBox(height: 16),

          // Inspection Status Card
          _buildInspectionStatusCard(context, vehicle),
          const SizedBox(height: 16),

          // Quick Action: Monthly Inspection Button
          _buildMonthlyInspectionButton(context, vehicle),
          const SizedBox(height: 16),

          // Recent Inspections Section
          _buildRecentInspections(context),
        ],
      ),
    );
  }

  Widget _buildVehicleInfoCard(BuildContext context, VehicleModel vehicle) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.directions_car,
                  color: Theme.of(context).primaryColor,
                  size: 28,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        vehicle.fullName,
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      Text(
                        vehicle.registration,
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              color: Theme.of(context).primaryColor,
                              fontWeight: FontWeight.w600,
                            ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            _buildVehicleInfoRow(
              context,
              icon: Icons.calendar_today,
              label: 'Assigned Since',
              value: vehicle.assignedAt != null
                  ? _formatDate(vehicle.assignedAt!)
                  : 'N/A',
            ),
            if (vehicle.vin != null)
              _buildVehicleInfoRow(
                context,
                icon: Icons.confirmation_number,
                label: 'VIN',
                value: vehicle.vin!,
              ),
            if (vehicle.odometerReading != null)
              _buildVehicleInfoRow(
                context,
                icon: Icons.speed,
                label: 'Odometer',
                value: '${NumberFormat('#,###').format(vehicle.odometerReading)} km',
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildInspectionStatusCard(BuildContext context, VehicleModel vehicle) {
    final isOverdue = vehicle.isInspectionDue;
    final isDueSoon = vehicle.isInspectionDueSoon;

    Color statusColor;
    String statusText;
    IconData statusIcon;

    if (isOverdue) {
      statusColor = Colors.red;
      statusText = 'Inspection Overdue!';
      statusIcon = Icons.warning;
    } else if (isDueSoon) {
      statusColor = Colors.orange;
      statusText = 'Inspection Due Soon';
      statusIcon = Icons.schedule;
    } else {
      statusColor = Colors.green;
      statusText = 'Inspection Up to Date';
      statusIcon = Icons.check_circle;
    }

    return Card(
      color: statusColor.withAlpha(25),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(statusIcon, color: statusColor, size: 28),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    statusText,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: statusColor,
                        ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (vehicle.lastInspectionDate != null)
              _buildInfoRow(
                context,
                label: 'Last Inspection',
                value: _formatDate(vehicle.lastInspectionDate!),
                color: statusColor,
              ),
            if (vehicle.nextInspectionDue != null)
              _buildInfoRow(
                context,
                label: 'Next Inspection Due',
                value: _formatDate(vehicle.nextInspectionDue!),
                color: statusColor,
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildMonthlyInspectionButton(BuildContext context, VehicleModel vehicle) {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton.icon(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => MonthlyInspectionFormScreen(vehicle: vehicle),
            ),
          );
        },
        icon: const Icon(Icons.assignment_turned_in),
        label: const Text('Perform Monthly Inspection'),
        style: ElevatedButton.styleFrom(
          padding: const EdgeInsets.symmetric(vertical: 16),
          backgroundColor: Theme.of(context).primaryColor,
          foregroundColor: Colors.white,
        ),
      ),
    );
  }

  Widget _buildRecentInspections(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Recent Inspections',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 12),
            Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    Icon(
                      Icons.history,
                      size: 48,
                      color: Colors.grey[400],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'No inspections yet',
                      style: TextStyle(
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildVehicleInfoRow(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey[600]),
          const SizedBox(width: 12),
          Expanded(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[600],
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(
    BuildContext context, {
    required String label,
    required String value,
    required Color color,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 14,
              color: color.withAlpha(180),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('dd MMM yyyy').format(date);
    } catch (e) {
      return dateString;
    }
  }
}
