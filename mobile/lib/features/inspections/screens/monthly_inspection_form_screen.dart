import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../vehicles/models/vehicle_model.dart';
import '../models/inspection_model.dart';

/// Monthly vehicle inspection form screen
class MonthlyInspectionFormScreen extends ConsumerStatefulWidget {
  final VehicleModel vehicle;

  const MonthlyInspectionFormScreen({
    super.key,
    required this.vehicle,
  });

  @override
  ConsumerState<MonthlyInspectionFormScreen> createState() =>
      _MonthlyInspectionFormScreenState();
}

class _MonthlyInspectionFormScreenState
    extends ConsumerState<MonthlyInspectionFormScreen> {
  final _formKey = GlobalKey<FormState>();

  // Form state
  DateTime _inspectionDate = DateTime.now();
  String _fuelLevel = 'half';
  int? _odometerReading;
  final TextEditingController _notesController = TextEditingController();

  // Checklist state
  final Map<String, bool> _checklist = {
    'lights': false,
    'wipers': false,
    'horn': false,
    'mirrors': false,
    'tires': false,
    'brakes': false,
    'signals': false,
    'seatbelts': false,
    'fluids': false,
    'bodyCondition': false,
    'interior': false,
    'documentation': false,
  };

  // Issues list
  final List<InspectionIssue> _issues = [];

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Monthly Inspection'),
        actions: [
          IconButton(
            icon: const Icon(Icons.info_outline),
            tooltip: 'Inspection Guidelines',
            onPressed: _showGuidelines,
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Vehicle Information Header
            _buildVehicleHeader(),
            const SizedBox(height: 24),

            // Inspection Date and Odometer
            _buildBasicInfo(),
            const SizedBox(height: 24),

            // Fuel Level
            _buildFuelLevel(),
            const SizedBox(height: 24),

            // Checklist Section
            _buildChecklistSection(),
            const SizedBox(height: 24),

            // Issues Section
            _buildIssuesSection(),
            const SizedBox(height: 24),

            // Notes Section
            _buildNotesSection(),
            const SizedBox(height: 24),

            // Signature Placeholder
            _buildSignatureSection(),
            const SizedBox(height: 24),

            // Photo Upload Placeholder
            _buildPhotosSection(),
            const SizedBox(height: 32),

            // Submit Button
            _buildSubmitButton(),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildVehicleHeader() {
    return Card(
      color: Theme.of(context).primaryColor.withAlpha(25),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Icon(
              Icons.directions_car,
              size: 40,
              color: Theme.of(context).primaryColor,
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    widget.vehicle.fullName,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    widget.vehicle.registration,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          color: Theme.of(context).primaryColor,
                        ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBasicInfo() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Inspection Details',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.calendar_today),
              title: const Text('Inspection Date'),
              subtitle: Text(DateFormat('dd MMM yyyy').format(_inspectionDate)),
              trailing: IconButton(
                icon: const Icon(Icons.edit),
                onPressed: () async {
                  final date = await showDatePicker(
                    context: context,
                    initialDate: _inspectionDate,
                    firstDate: DateTime.now().subtract(const Duration(days: 30)),
                    lastDate: DateTime.now(),
                  );
                  if (date != null) {
                    setState(() => _inspectionDate = date);
                  }
                },
              ),
            ),
            const Divider(),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: TextFormField(
                decoration: const InputDecoration(
                  labelText: 'Odometer Reading (km)',
                  prefixIcon: Icon(Icons.speed),
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.number,
                initialValue: widget.vehicle.odometerReading?.toString(),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter odometer reading';
                  }
                  final reading = int.tryParse(value);
                  if (reading == null) {
                    return 'Please enter a valid number';
                  }
                  if (widget.vehicle.odometerReading != null &&
                      reading < widget.vehicle.odometerReading!) {
                    return 'Reading cannot be less than previous: ${widget.vehicle.odometerReading}';
                  }
                  return null;
                },
                onSaved: (value) {
                  _odometerReading = int.tryParse(value ?? '');
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFuelLevel() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Fuel Level',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _fuelLevel,
              decoration: const InputDecoration(
                prefixIcon: Icon(Icons.local_gas_station),
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(value: 'empty', child: Text('Empty')),
                DropdownMenuItem(value: 'quarter', child: Text('1/4 Tank')),
                DropdownMenuItem(value: 'half', child: Text('1/2 Tank')),
                DropdownMenuItem(
                    value: 'three_quarters', child: Text('3/4 Tank')),
                DropdownMenuItem(value: 'full', child: Text('Full Tank')),
              ],
              onChanged: (value) {
                if (value != null) {
                  setState(() => _fuelLevel = value);
                }
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChecklistSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Safety Checklist',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                Text(
                  '${_checklist.values.where((v) => v).length}/12',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: Theme.of(context).primaryColor,
                        fontWeight: FontWeight.bold,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            const Text(
              'Check each item carefully. Report any issues below.',
              style: TextStyle(fontSize: 13, color: Colors.grey),
            ),
            const SizedBox(height: 16),
            ..._buildChecklistItems(),
          ],
        ),
      ),
    );
  }

  List<Widget> _buildChecklistItems() {
    final items = [
      {'key': 'lights', 'label': 'All Lights Working', 'icon': Icons.lightbulb},
      {'key': 'wipers', 'label': 'Wipers Functional', 'icon': Icons.water_drop},
      {'key': 'horn', 'label': 'Horn Working', 'icon': Icons.volume_up},
      {'key': 'mirrors', 'label': 'Mirrors Clean & Intact', 'icon': Icons.remove_red_eye},
      {'key': 'tires', 'label': 'Tire Condition & Pressure', 'icon': Icons.album},
      {'key': 'brakes', 'label': 'Brake Response', 'icon': Icons.warning},
      {'key': 'signals', 'label': 'Turn Signals Working', 'icon': Icons.turn_right},
      {'key': 'seatbelts', 'label': 'Seatbelts Functional', 'icon': Icons.airline_seat_recline_normal},
      {'key': 'fluids', 'label': 'Fluid Levels (Oil, Coolant, Washer)', 'icon': Icons.opacity},
      {'key': 'bodyCondition', 'label': 'Body Damage Check', 'icon': Icons.car_repair},
      {'key': 'interior', 'label': 'Interior Cleanliness', 'icon': Icons.cleaning_services},
      {'key': 'documentation', 'label': 'Registration & Insurance Docs', 'icon': Icons.description},
    ];

    return items.map((item) {
      return CheckboxListTile(
        key: Key(item['key'] as String),
        secondary: Icon(item['icon'] as IconData),
        title: Text(item['label'] as String),
        value: _checklist[item['key'] as String] ?? false,
        onChanged: (bool? value) {
          setState(() {
            _checklist[item['key'] as String] = value ?? false;
          });
        },
      );
    }).toList();
  }

  Widget _buildIssuesSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Issues Found',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                TextButton.icon(
                  icon: const Icon(Icons.add),
                  label: const Text('Add Issue'),
                  onPressed: _addIssue,
                ),
              ],
            ),
            const SizedBox(height: 8),
            if (_issues.isEmpty)
              const Padding(
                padding: EdgeInsets.all(16),
                child: Center(
                  child: Text(
                    'No issues reported',
                    style: TextStyle(color: Colors.grey),
                  ),
                ),
              )
            else
              ..._issues.asMap().entries.map((entry) {
                final index = entry.key;
                final issue = entry.value;
                return Card(
                  color: issue.severityColor.withAlpha(25),
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    leading: Icon(
                      issue.requiresImmediateAction
                          ? Icons.warning
                          : Icons.info,
                      color: issue.severityColor,
                    ),
                    title: Text(issue.description),
                    subtitle: Text(
                      '${issue.category} - ${issue.severity.toUpperCase()}',
                      style: TextStyle(color: issue.severityColor),
                    ),
                    trailing: IconButton(
                      icon: const Icon(Icons.delete),
                      onPressed: () {
                        setState(() => _issues.removeAt(index));
                      },
                    ),
                  ),
                );
              }).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildNotesSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Additional Notes',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _notesController,
              decoration: const InputDecoration(
                hintText: 'Any additional comments or observations...',
                border: OutlineInputBorder(),
              ),
              maxLines: 4,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSignatureSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Inspector Signature',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            Container(
              height: 150,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.draw, size: 40, color: Colors.grey[400]),
                    const SizedBox(height: 8),
                    Text(
                      'Signature capture - Coming soon',
                      style: TextStyle(color: Colors.grey[600]),
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

  Widget _buildPhotosSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Photos',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              icon: const Icon(Icons.camera_alt),
              label: const Text('Add Photos'),
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Photo upload - Coming soon'),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton.icon(
        onPressed: _submitInspection,
        icon: const Icon(Icons.check_circle),
        label: const Text('Submit Inspection'),
        style: ElevatedButton.styleFrom(
          padding: const EdgeInsets.symmetric(vertical: 16),
          backgroundColor: Theme.of(context).primaryColor,
          foregroundColor: Colors.white,
        ),
      ),
    );
  }

  void _addIssue() {
    showDialog(
      context: context,
      builder: (context) => _AddIssueDialog(
        onAdd: (issue) {
          setState(() => _issues.add(issue));
        },
      ),
    );
  }

  void _showGuidelines() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Inspection Guidelines'),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildGuideline(
                'Frequency',
                'Perform monthly inspections or before long trips',
              ),
              _buildGuideline(
                'Checklist',
                'Complete all 12 items. Report any failures as issues',
              ),
              _buildGuideline(
                'Issues',
                'Document all problems with appropriate severity levels',
              ),
              _buildGuideline(
                'Critical Issues',
                'Do not operate vehicle if critical safety issues are found',
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  Widget _buildGuideline(String title, String description) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(description),
        ],
      ),
    );
  }

  void _submitInspection() {
    if (!_formKey.currentState!.validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please complete all required fields'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    _formKey.currentState!.save();

    // Create inspection object
    final inspection = VehicleInspectionModel(
      vehicleId: widget.vehicle.id,
      inspectorId: 'current_user_id', // TODO: Get from auth provider
      inspectionDate: _inspectionDate,
      odometerReading: _odometerReading!,
      fuelLevel: _fuelLevel,
      checklist: InspectionChecklist(
        lights: _checklist['lights']!,
        wipers: _checklist['wipers']!,
        horn: _checklist['horn']!,
        mirrors: _checklist['mirrors']!,
        tires: _checklist['tires']!,
        brakes: _checklist['brakes']!,
        signals: _checklist['signals']!,
        seatbelts: _checklist['seatbelts']!,
        fluids: _checklist['fluids']!,
        bodyCondition: _checklist['bodyCondition']!,
        interior: _checklist['interior']!,
        documentation: _checklist['documentation']!,
      ),
      issues: _issues,
      notes: _notesController.text.isEmpty ? null : _notesController.text,
      status: _issues.any((i) => i.requiresImmediateAction)
          ? 'requires_attention'
          : _checklist.values.every((v) => v)
              ? 'completed'
              : 'requires_attention',
    );

    // TODO: Submit to API
    // For now, show success message
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Inspection Submitted'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Vehicle: ${widget.vehicle.fullName}'),
            Text('Date: ${DateFormat('dd MMM yyyy').format(_inspectionDate)}'),
            Text('Status: ${inspection.status}'),
            const SizedBox(height: 8),
            Text(
              'Checklist: ${_checklist.values.where((v) => v).length}/12 passed',
            ),
            if (_issues.isNotEmpty) Text('Issues found: ${_issues.length}'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context); // Close dialog
              Navigator.pop(context); // Return to vehicle screen
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }
}

/// Dialog for adding inspection issues
class _AddIssueDialog extends StatefulWidget {
  final Function(InspectionIssue) onAdd;

  const _AddIssueDialog({required this.onAdd});

  @override
  State<_AddIssueDialog> createState() => _AddIssueDialogState();
}

class _AddIssueDialogState extends State<_AddIssueDialog> {
  final _formKey = GlobalKey<FormState>();
  String _category = 'lights';
  String _severity = 'medium';
  bool _requiresImmediateAction = false;
  final TextEditingController _descriptionController = TextEditingController();

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Add Issue'),
      content: Form(
        key: _formKey,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                value: _category,
                decoration: const InputDecoration(
                  labelText: 'Category',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem(value: 'lights', child: Text('Lights')),
                  DropdownMenuItem(value: 'wipers', child: Text('Wipers')),
                  DropdownMenuItem(value: 'horn', child: Text('Horn')),
                  DropdownMenuItem(value: 'mirrors', child: Text('Mirrors')),
                  DropdownMenuItem(value: 'tires', child: Text('Tires')),
                  DropdownMenuItem(value: 'brakes', child: Text('Brakes')),
                  DropdownMenuItem(value: 'signals', child: Text('Signals')),
                  DropdownMenuItem(value: 'seatbelts', child: Text('Seatbelts')),
                  DropdownMenuItem(value: 'fluids', child: Text('Fluids')),
                  DropdownMenuItem(value: 'body', child: Text('Body Condition')),
                  DropdownMenuItem(value: 'interior', child: Text('Interior')),
                  DropdownMenuItem(value: 'documentation', child: Text('Documentation')),
                  DropdownMenuItem(value: 'other', child: Text('Other')),
                ],
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _category = value);
                  }
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descriptionController,
                decoration: const InputDecoration(
                  labelText: 'Description',
                  border: OutlineInputBorder(),
                  hintText: 'Describe the issue...',
                ),
                maxLines: 3,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please describe the issue';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: _severity,
                decoration: const InputDecoration(
                  labelText: 'Severity',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem(value: 'low', child: Text('Low')),
                  DropdownMenuItem(value: 'medium', child: Text('Medium')),
                  DropdownMenuItem(value: 'high', child: Text('High')),
                  DropdownMenuItem(value: 'critical', child: Text('Critical')),
                ],
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _severity = value);
                  }
                },
              ),
              const SizedBox(height: 16),
              CheckboxListTile(
                title: const Text('Requires Immediate Action'),
                subtitle: const Text('Vehicle should not be operated'),
                value: _requiresImmediateAction,
                onChanged: (value) {
                  setState(() => _requiresImmediateAction = value ?? false);
                },
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () {
            if (_formKey.currentState!.validate()) {
              final issue = InspectionIssue(
                category: _category,
                description: _descriptionController.text,
                severity: _severity,
                requiresImmediateAction: _requiresImmediateAction,
              );
              widget.onAdd(issue);
              Navigator.pop(context);
            }
          },
          child: const Text('Add'),
        ),
      ],
    );
  }
}
