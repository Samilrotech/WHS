import 'dart:convert';

/// User model matching the Laravel API UserResource
class UserModel {
  final String id;
  final String employeeId;
  final String name;
  final String email;
  final String? phone;
  final String position;
  final String? employmentStatus;
  final String? employmentStartDate;
  final bool? isActive;
  final BranchModel? branch;
  final String role;
  final EmergencyContactModel? emergencyContact;
  final String? createdAt;
  final String? updatedAt;

  UserModel({
    required this.id,
    required this.employeeId,
    required this.name,
    required this.email,
    this.phone,
    required this.position,
    this.employmentStatus,
    this.employmentStartDate,
    this.isActive,
    this.branch,
    required this.role,
    this.emergencyContact,
    this.createdAt,
    this.updatedAt,
  });

  /// Create UserModel from JSON
  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as String,
      employeeId: json['employee_id'] as String,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      position: json['position'] as String,
      employmentStatus: json['employment_status'] as String?,
      employmentStartDate: json['employment_start_date'] as String?,
      isActive: json['is_active'] as bool?,
      branch: json['branch'] != null
          ? BranchModel.fromJson(json['branch'] as Map<String, dynamic>)
          : null,
      role: json['role'] as String,
      emergencyContact: json['emergency_contact'] != null
          ? EmergencyContactModel.fromJson(
              json['emergency_contact'] as Map<String, dynamic>,
            )
          : null,
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }

  /// Convert UserModel to JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'employee_id': employeeId,
      'name': name,
      'email': email,
      'phone': phone,
      'position': position,
      'employment_status': employmentStatus,
      'employment_start_date': employmentStartDate,
      'is_active': isActive,
      'branch': branch?.toJson(),
      'role': role,
      'emergency_contact': emergencyContact?.toJson(),
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  /// Convert UserModel to JSON string
  String toJsonString() => json.encode(toJson());

  /// Create UserModel from JSON string
  factory UserModel.fromJsonString(String jsonString) {
    return UserModel.fromJson(json.decode(jsonString) as Map<String, dynamic>);
  }

  /// Copy with method for state updates
  UserModel copyWith({
    String? id,
    String? employeeId,
    String? name,
    String? email,
    String? phone,
    String? position,
    String? employmentStatus,
    String? employmentStartDate,
    bool? isActive,
    BranchModel? branch,
    String? role,
    EmergencyContactModel? emergencyContact,
    String? createdAt,
    String? updatedAt,
  }) {
    return UserModel(
      id: id ?? this.id,
      employeeId: employeeId ?? this.employeeId,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      position: position ?? this.position,
      employmentStatus: employmentStatus ?? this.employmentStatus,
      employmentStartDate: employmentStartDate ?? this.employmentStartDate,
      isActive: isActive ?? this.isActive,
      branch: branch ?? this.branch,
      role: role ?? this.role,
      emergencyContact: emergencyContact ?? this.emergencyContact,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

/// Branch model
class BranchModel {
  final String id;
  final String name;

  BranchModel({
    required this.id,
    required this.name,
  });

  factory BranchModel.fromJson(Map<String, dynamic> json) {
    return BranchModel(
      id: json['id'] as String,
      name: json['name'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
    };
  }
}

/// Emergency contact model
class EmergencyContactModel {
  final String? name;
  final String? phone;

  EmergencyContactModel({
    this.name,
    this.phone,
  });

  factory EmergencyContactModel.fromJson(Map<String, dynamic> json) {
    return EmergencyContactModel(
      name: json['name'] as String?,
      phone: json['phone'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'phone': phone,
    };
  }

  bool get hasContact => name != null && phone != null;
}
