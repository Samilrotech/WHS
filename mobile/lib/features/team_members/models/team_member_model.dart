/// Team member model matching the Laravel API UserResource
class TeamMemberModel {
  final String id;
  final String employeeId;
  final String name;
  final String email;
  final String? phone;
  final String position;
  final String employmentStatus;
  final String? employmentStartDate;
  final bool isActive;
  final BranchInfo? branch;
  final String role;
  final VehicleInfo? currentVehicle;
  final EmergencyContactInfo emergencyContact;
  final String? createdAt;
  final String? updatedAt;

  TeamMemberModel({
    required this.id,
    required this.employeeId,
    required this.name,
    required this.email,
    this.phone,
    required this.position,
    required this.employmentStatus,
    this.employmentStartDate,
    required this.isActive,
    this.branch,
    required this.role,
    this.currentVehicle,
    required this.emergencyContact,
    this.createdAt,
    this.updatedAt,
  });

  factory TeamMemberModel.fromJson(Map<String, dynamic> json) {
    return TeamMemberModel(
      id: json['id'] as String,
      employeeId: json['employee_id'] as String,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      position: json['position'] as String,
      employmentStatus: json['employment_status'] as String,
      employmentStartDate: json['employment_start_date'] as String?,
      isActive: json['is_active'] as bool,
      branch: json['branch'] != null
          ? BranchInfo.fromJson(json['branch'] as Map<String, dynamic>)
          : null,
      role: json['role'] as String,
      currentVehicle: json['current_vehicle'] != null
          ? VehicleInfo.fromJson(json['current_vehicle'] as Map<String, dynamic>)
          : null,
      emergencyContact: EmergencyContactInfo.fromJson(
        json['emergency_contact'] as Map<String, dynamic>,
      ),
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }

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
      'current_vehicle': currentVehicle?.toJson(),
      'emergency_contact': emergencyContact.toJson(),
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}

/// Branch information
class BranchInfo {
  final String id;
  final String name;

  BranchInfo({
    required this.id,
    required this.name,
  });

  factory BranchInfo.fromJson(Map<String, dynamic> json) {
    return BranchInfo(
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

/// Vehicle information
class VehicleInfo {
  final String id;
  final String registration;
  final String make;
  final String model;
  final String? assignedAt;

  VehicleInfo({
    required this.id,
    required this.registration,
    required this.make,
    required this.model,
    this.assignedAt,
  });

  factory VehicleInfo.fromJson(Map<String, dynamic> json) {
    return VehicleInfo(
      id: json['id'] as String,
      registration: json['registration'] as String,
      make: json['make'] as String,
      model: json['model'] as String,
      assignedAt: json['assigned_at'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'registration': registration,
      'make': make,
      'model': model,
      'assigned_at': assignedAt,
    };
  }

  String get fullName => '$make $model';
}

/// Emergency contact information
class EmergencyContactInfo {
  final String? name;
  final String? phone;

  EmergencyContactInfo({
    this.name,
    this.phone,
  });

  factory EmergencyContactInfo.fromJson(Map<String, dynamic> json) {
    return EmergencyContactInfo(
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

/// Pagination metadata
class PaginationMeta {
  final int currentPage;
  final int from;
  final int lastPage;
  final int perPage;
  final int to;
  final int total;

  PaginationMeta({
    required this.currentPage,
    required this.from,
    required this.lastPage,
    required this.perPage,
    required this.to,
    required this.total,
  });

  factory PaginationMeta.fromJson(Map<String, dynamic> json) {
    // Helper function to extract int from either int or List<int>
    int extractInt(dynamic value) {
      if (value is int) return value;
      if (value is List && value.isNotEmpty) return value.first as int;
      return 0;
    }

    return PaginationMeta(
      currentPage: extractInt(json['current_page']),
      from: extractInt(json['from']),
      lastPage: extractInt(json['last_page']),
      perPage: extractInt(json['per_page']),
      to: extractInt(json['to']),
      total: extractInt(json['total']),
    );
  }

  bool get hasMore => currentPage < lastPage;
}

/// Team members list response
class TeamMembersResponse {
  final List<TeamMemberModel> data;
  final PaginationMeta meta;

  TeamMembersResponse({
    required this.data,
    required this.meta,
  });

  factory TeamMembersResponse.fromJson(Map<String, dynamic> json) {
    return TeamMembersResponse(
      data: (json['data'] as List)
          .map((item) => TeamMemberModel.fromJson(item as Map<String, dynamic>))
          .toList(),
      meta: PaginationMeta.fromJson(json['meta'] as Map<String, dynamic>),
    );
  }
}
