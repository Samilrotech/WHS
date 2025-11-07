import 'dart:convert';
import 'package:flutter/material.dart';

/// Monthly vehicle inspection model
class VehicleInspectionModel {
  final String? id;
  final String vehicleId;
  final String inspectorId;
  final String? inspectorName;
  final DateTime inspectionDate;
  final int odometerReading;
  final String fuelLevel; // empty, quarter, half, three_quarters, full
  final InspectionChecklist checklist;
  final List<InspectionIssue> issues;
  final String? notes;
  final String? signature;
  final List<String>? photoUrls;
  final String status; // pending, completed, requires_attention
  final String? createdAt;
  final String? updatedAt;

  VehicleInspectionModel({
    this.id,
    required this.vehicleId,
    required this.inspectorId,
    this.inspectorName,
    required this.inspectionDate,
    required this.odometerReading,
    required this.fuelLevel,
    required this.checklist,
    this.issues = const [],
    this.notes,
    this.signature,
    this.photoUrls,
    this.status = 'pending',
    this.createdAt,
    this.updatedAt,
  });

  factory VehicleInspectionModel.fromJson(Map<String, dynamic> json) {
    return VehicleInspectionModel(
      id: json['id'] as String?,
      vehicleId: json['vehicle_id'] as String,
      inspectorId: json['inspector_id'] as String,
      inspectorName: json['inspector_name'] as String?,
      inspectionDate: DateTime.parse(json['inspection_date'] as String),
      odometerReading: json['odometer_reading'] as int,
      fuelLevel: json['fuel_level'] as String,
      checklist: InspectionChecklist.fromJson(
        json['checklist'] as Map<String, dynamic>,
      ),
      issues: (json['issues'] as List<dynamic>?)
              ?.map((item) =>
                  InspectionIssue.fromJson(item as Map<String, dynamic>))
              .toList() ??
          [],
      notes: json['notes'] as String?,
      signature: json['signature'] as String?,
      photoUrls: (json['photo_urls'] as List<dynamic>?)
          ?.map((url) => url as String)
          .toList(),
      status: json['status'] as String? ?? 'pending',
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      if (id != null) 'id': id,
      'vehicle_id': vehicleId,
      'inspector_id': inspectorId,
      'inspector_name': inspectorName,
      'inspection_date': inspectionDate.toIso8601String(),
      'odometer_reading': odometerReading,
      'fuel_level': fuelLevel,
      'checklist': checklist.toJson(),
      'issues': issues.map((issue) => issue.toJson()).toList(),
      'notes': notes,
      'signature': signature,
      'photo_urls': photoUrls,
      'status': status,
      if (createdAt != null) 'created_at': createdAt,
      if (updatedAt != null) 'updated_at': updatedAt,
    };
  }

  String toJsonString() => json.encode(toJson());

  factory VehicleInspectionModel.fromJsonString(String jsonString) {
    return VehicleInspectionModel.fromJson(
      json.decode(jsonString) as Map<String, dynamic>,
    );
  }

  bool get hasIssues => issues.isNotEmpty;

  bool get requiresAttention =>
      hasIssues || !checklist.allItemsPassed;
}

/// Inspection checklist items
class InspectionChecklist {
  final bool lights; // All lights working
  final bool wipers; // Wipers functional
  final bool horn; // Horn working
  final bool mirrors; // Mirrors clean and intact
  final bool tires; // Tire condition and pressure
  final bool brakes; // Brake response
  final bool signals; // Turn signals working
  final bool seatbelts; // Seatbelts functional
  final bool fluids; // Fluid levels (oil, coolant, washer)
  final bool bodyCondition; // Body damage check
  final bool interior; // Interior cleanliness
  final bool documentation; // Registration, insurance docs

  InspectionChecklist({
    this.lights = false,
    this.wipers = false,
    this.horn = false,
    this.mirrors = false,
    this.tires = false,
    this.brakes = false,
    this.signals = false,
    this.seatbelts = false,
    this.fluids = false,
    this.bodyCondition = false,
    this.interior = false,
    this.documentation = false,
  });

  factory InspectionChecklist.fromJson(Map<String, dynamic> json) {
    return InspectionChecklist(
      lights: json['lights'] as bool? ?? false,
      wipers: json['wipers'] as bool? ?? false,
      horn: json['horn'] as bool? ?? false,
      mirrors: json['mirrors'] as bool? ?? false,
      tires: json['tires'] as bool? ?? false,
      brakes: json['brakes'] as bool? ?? false,
      signals: json['signals'] as bool? ?? false,
      seatbelts: json['seatbelts'] as bool? ?? false,
      fluids: json['fluids'] as bool? ?? false,
      bodyCondition: json['body_condition'] as bool? ?? false,
      interior: json['interior'] as bool? ?? false,
      documentation: json['documentation'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'lights': lights,
      'wipers': wipers,
      'horn': horn,
      'mirrors': mirrors,
      'tires': tires,
      'brakes': brakes,
      'signals': signals,
      'seatbelts': seatbelts,
      'fluids': fluids,
      'body_condition': bodyCondition,
      'interior': interior,
      'documentation': documentation,
    };
  }

  bool get allItemsPassed =>
      lights &&
      wipers &&
      horn &&
      mirrors &&
      tires &&
      brakes &&
      signals &&
      seatbelts &&
      fluids &&
      bodyCondition &&
      interior &&
      documentation;

  int get passedCount {
    int count = 0;
    if (lights) count++;
    if (wipers) count++;
    if (horn) count++;
    if (mirrors) count++;
    if (tires) count++;
    if (brakes) count++;
    if (signals) count++;
    if (seatbelts) count++;
    if (fluids) count++;
    if (bodyCondition) count++;
    if (interior) count++;
    if (documentation) count++;
    return count;
  }

  static const int totalItems = 12;
}

/// Individual inspection issue
class InspectionIssue {
  final String category; // lights, brakes, tires, etc.
  final String description;
  final String severity; // low, medium, high, critical
  final bool requiresImmediateAction;

  InspectionIssue({
    required this.category,
    required this.description,
    required this.severity,
    this.requiresImmediateAction = false,
  });

  factory InspectionIssue.fromJson(Map<String, dynamic> json) {
    return InspectionIssue(
      category: json['category'] as String,
      description: json['description'] as String,
      severity: json['severity'] as String,
      requiresImmediateAction: json['requires_immediate_action'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'category': category,
      'description': description,
      'severity': severity,
      'requires_immediate_action': requiresImmediateAction,
    };
  }

  Color get severityColor {
    switch (severity) {
      case 'critical':
        return const Color(0xFFD32F2F); // Red 700
      case 'high':
        return const Color(0xFFF57C00); // Orange 700
      case 'medium':
        return const Color(0xFFFBC02D); // Yellow 700
      case 'low':
        return const Color(0xFF388E3C); // Green 700
      default:
        return const Color(0xFF757575); // Grey 600
    }
  }
}
