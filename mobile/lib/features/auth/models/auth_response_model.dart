import 'user_model.dart';

/// Authentication response model for login endpoint
class AuthResponseModel {
  final String token;
  final String? expiresAt;
  final UserModel user;

  AuthResponseModel({
    required this.token,
    this.expiresAt,
    required this.user,
  });

  /// Create AuthResponseModel from JSON
  factory AuthResponseModel.fromJson(Map<String, dynamic> json) {
    return AuthResponseModel(
      token: json['token'] as String,
      expiresAt: json['expires_at'] as String?,
      user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
    );
  }

  /// Convert AuthResponseModel to JSON
  Map<String, dynamic> toJson() {
    return {
      'token': token,
      'expires_at': expiresAt,
      'user': user.toJson(),
    };
  }
}

/// Error response model for API errors
class ErrorResponseModel {
  final String message;
  final Map<String, List<String>>? errors;

  ErrorResponseModel({
    required this.message,
    this.errors,
  });

  /// Create ErrorResponseModel from JSON
  factory ErrorResponseModel.fromJson(Map<String, dynamic> json) {
    Map<String, List<String>>? errors;
    if (json['errors'] != null) {
      errors = {};
      (json['errors'] as Map<String, dynamic>).forEach((key, value) {
        errors![key] = List<String>.from(value as List);
      });
    }

    return ErrorResponseModel(
      message: json['message'] as String,
      errors: errors,
    );
  }

  /// Get first error message from errors map
  String? get firstError {
    if (errors == null || errors!.isEmpty) return null;
    return errors!.values.first.first;
  }

  /// Get all error messages as a single string
  String get allErrors {
    if (errors == null || errors!.isEmpty) return message;

    final errorMessages = <String>[];
    errors!.forEach((key, messages) {
      errorMessages.addAll(messages);
    });

    return errorMessages.join('\n');
  }
}
