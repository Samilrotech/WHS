import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../providers/team_members_provider.dart';
import '../models/team_member_model.dart';

class TeamMemberDetailScreen extends ConsumerWidget {
  final String memberId;

  const TeamMemberDetailScreen({
    super.key,
    required this.memberId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final memberAsync = ref.watch(teamMemberDetailProvider(memberId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Team Member Details'),
      ),
      body: memberAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline,
                size: 64,
                color: Colors.red,
              ),
              const SizedBox(height: 16),
              Text(
                'Error loading team member details',
                style: const TextStyle(fontSize: 16),
              ),
              const SizedBox(height: 8),
              Text(
                error.toString(),
                style: const TextStyle(fontSize: 12, color: Colors.grey),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
        data: (member) => _buildContent(context, member),
      ),
    );
  }

  Widget _buildContent(BuildContext context, TeamMemberModel member) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Profile Card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 50,
                    backgroundColor: Theme.of(context).primaryColor,
                    child: Text(
                      member.name.substring(0, 1).toUpperCase(),
                      style: const TextStyle(
                        fontSize: 36,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    member.name,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    member.position,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          color: Colors.grey[600],
                        ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  _buildStatusChip(context, member),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Contact Information
          _buildSection(
            context,
            title: 'Contact Information',
            icon: Icons.contact_mail,
            children: [
              _buildInfoRow(
                context,
                icon: Icons.badge,
                label: 'Employee ID',
                value: member.employeeId,
              ),
              _buildInfoRow(
                context,
                icon: Icons.email,
                label: 'Email',
                value: member.email,
              ),
              if (member.phone != null)
                _buildInfoRow(
                  context,
                  icon: Icons.phone,
                  label: 'Phone',
                  value: member.phone!,
                ),
            ],
          ),
          const SizedBox(height: 16),

          // Employment Details
          _buildSection(
            context,
            title: 'Employment Details',
            icon: Icons.work,
            children: [
              _buildInfoRow(
                context,
                icon: Icons.location_city,
                label: 'Branch',
                value: member.branch?.name ?? 'Not assigned',
              ),
              _buildInfoRow(
                context,
                icon: Icons.work_outline,
                label: 'Status',
                value: _formatEmploymentStatus(member.employmentStatus),
              ),
              _buildInfoRow(
                context,
                icon: Icons.security,
                label: 'Role',
                value: member.role,
              ),
              if (member.employmentStartDate != null)
                _buildInfoRow(
                  context,
                  icon: Icons.calendar_today,
                  label: 'Start Date',
                  value: _formatDate(member.employmentStartDate!),
                ),
            ],
          ),
          const SizedBox(height: 16),

          // Vehicle Information (if assigned)
          if (member.currentVehicle != null)
            _buildSection(
              context,
              title: 'Assigned Vehicle',
              icon: Icons.directions_car,
              children: [
                _buildInfoRow(
                  context,
                  icon: Icons.confirmation_number,
                  label: 'Registration',
                  value: member.currentVehicle!.registration,
                ),
                _buildInfoRow(
                  context,
                  icon: Icons.car_rental,
                  label: 'Vehicle',
                  value: member.currentVehicle!.fullName,
                ),
                if (member.currentVehicle!.assignedAt != null)
                  _buildInfoRow(
                    context,
                    icon: Icons.access_time,
                    label: 'Assigned At',
                    value: _formatDateTime(member.currentVehicle!.assignedAt!),
                  ),
              ],
            ),
          if (member.currentVehicle != null) const SizedBox(height: 16),

          // Emergency Contact
          if (member.emergencyContact.hasContact)
            _buildSection(
              context,
              title: 'Emergency Contact',
              icon: Icons.emergency,
              children: [
                _buildInfoRow(
                  context,
                  icon: Icons.person,
                  label: 'Name',
                  value: member.emergencyContact.name ?? 'N/A',
                ),
                _buildInfoRow(
                  context,
                  icon: Icons.phone,
                  label: 'Phone',
                  value: member.emergencyContact.phone ?? 'N/A',
                ),
              ],
            ),
        ],
      ),
    );
  }

  Widget _buildStatusChip(BuildContext context, TeamMemberModel member) {
    final color = member.isActive ? Colors.green : Colors.red;
    return Chip(
      label: Text(
        member.isActive ? 'Active' : 'Inactive',
        style: const TextStyle(color: Colors.white),
      ),
      backgroundColor: color,
    );
  }

  Widget _buildSection(
    BuildContext context, {
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: Theme.of(context).primaryColor),
                const SizedBox(width: 8),
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
              ],
            ),
            const Divider(),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: Colors.grey[600]),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _formatEmploymentStatus(String status) {
    return status.replaceAll('_', ' ').split(' ').map((word) {
      return word[0].toUpperCase() + word.substring(1).toLowerCase();
    }).join(' ');
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('dd MMM yyyy').format(date);
    } catch (e) {
      return dateString;
    }
  }

  String _formatDateTime(String dateTimeString) {
    try {
      final dateTime = DateTime.parse(dateTimeString);
      return DateFormat('dd MMM yyyy, HH:mm').format(dateTime);
    } catch (e) {
      return dateTimeString;
    }
  }
}
