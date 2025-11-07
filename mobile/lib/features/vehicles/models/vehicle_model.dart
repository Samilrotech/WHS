import 'dart:convert';

/// Vehicle model matching Laravel API VehicleResource
class VehicleModel {
  final String id;
  final String registration;
  final String make;
  final String model;
  final int? year;
  final String? vin;
  final String status;
  final int? odometerReading;
  final String? assignedDriverId;
  final String? assignedDriverName;
  final String? assignedAt;
  final String? lastInspectionDate;
  final String? nextInspectionDue;
  final String? createdAt;
  final String? updatedAt;

  VehicleModel({
    required this.id,
    required this.registration,
    required this.make,
    required this.model,
    this.year,
    this.vin,
    required this.status,
    this.odometerReading,
    this.assignedDriverId,
    this.assignedDriverName,
    this.assignedAt,
    this.lastInspectionDate,
    this.nextInspectionDue,
    this.createdAt,
    this.updatedAt,
  });

  factory VehicleModel.fromJson(Map<String, dynamic> json) {
    return VehicleModel(
      id: json['id'] as String,
      registration: json['registration'] as String,
      make: json['make'] as String,
      model: json['model'] as String,
      year: json['year'] as int?,
      vin: json['vin'] as String?,
      status: json['status'] as String? ?? 'active',
      odometerReading: json['odometer_reading'] as int?,
      assignedDriverId: json['assigned_driver_id'] as String?,
      assignedDriverName: json['assigned_driver_name'] as String?,
      assignedAt: json['assigned_at'] as String?,
      lastInspectionDate: json['last_inspection_date'] as String?,
      nextInspectionDue: json['next_inspection_due'] as String?,
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'registration': registration,
      'make': make,
      'model': model,
      'year': year,
      'vin': vin,
      'status': status,
      'odometer_reading': odometerReading,
      'assigned_driver_id': assignedDriverId,
      'assigned_driver_name': assignedDriverName,
      'assigned_at': assignedAt,
      'last_inspection_date': lastInspectionDate,
      'next_inspection_due': nextInspectionDue,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  String toJsonString() => json.encode(toJson());

  factory VehicleModel.fromJsonString(String jsonString) {
    return VehicleModel.fromJson(json.decode(jsonString) as Map<String, dynamic>);
  }

  String get fullName => '$make $model${year != null ? " ($year)" : ""}';

  bool get isInspectionDue {
    if (nextInspectionDue == null) return false;
    try {
      final dueDate = DateTime.parse(nextInspectionDue!);
      return DateTime.now().isAfter(dueDate);
    } catch (e) {
      return false;
    }
  }

  bool get isInspectionDueSoon {
    if (nextInspectionDue == null) return false;
    try {
      final dueDate = DateTime.parse(nextInspectionDue!);
      final daysUntilDue = dueDate.difference(DateTime.now()).inDays;
      return daysUntilDue <= 7 && daysUntilDue > 0;
    } catch (e) {
      return false;
    }
  }
}
