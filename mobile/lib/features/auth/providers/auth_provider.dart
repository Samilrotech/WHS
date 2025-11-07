import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../core/storage/secure_storage_service.dart';
import '../models/auth_response_model.dart';
import '../models/user_model.dart';

/// Authentication state
class AuthState {
  final bool isAuthenticated;
  final bool isLoading;
  final UserModel? user;
  final String? error;

  const AuthState({
    this.isAuthenticated = false,
    this.isLoading = false,
    this.user,
    this.error,
  });

  AuthState copyWith({
    bool? isAuthenticated,
    bool? isLoading,
    UserModel? user,
    String? error,
  }) {
    return AuthState(
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      isLoading: isLoading ?? this.isLoading,
      user: user ?? this.user,
      error: error,
    );
  }
}

/// Authentication provider
class AuthNotifier extends StateNotifier<AuthState> {
  final ApiClient _apiClient = ApiClient();
  final SecureStorageService _storage = SecureStorageService();

  AuthNotifier() : super(const AuthState()) {
    _checkAuthStatus();
  }

  /// Check if user is already authenticated on app start
  Future<void> _checkAuthStatus() async {
    try {
      final isAuthenticated = await _storage.isAuthenticated();

      if (isAuthenticated) {
        // Get stored user data
        final userDataString = await _storage.getUserData();
        if (userDataString != null) {
          final user = UserModel.fromJsonString(userDataString);
          state = state.copyWith(
            isAuthenticated: true,
            user: user,
          );

          // Verify token is still valid by fetching user
          await refreshUser();
        }
      }
    } catch (e) {
      print('Error checking auth status: $e');
      await logout();
    }
  }

  /// Login with email and password
  Future<void> login({
    required String email,
    required String password,
    bool rememberMe = false,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      // Get device name (you can customize this)
      final deviceName = 'Android Device';

      // Call login API
      final response = await _apiClient.login(
        email: email,
        password: password,
        deviceName: deviceName,
      );

      // Parse response
      final authResponse = AuthResponseModel.fromJson(response.data);

      // Save token and user data
      await _storage.saveToken(authResponse.token);
      await _storage.saveUserData(authResponse.user.toJsonString());
      await _storage.setRememberMe(rememberMe);

      // Update state
      state = state.copyWith(
        isAuthenticated: true,
        isLoading: false,
        user: authResponse.user,
        error: null,
      );
    } on DioException catch (e) {
      String errorMessage = 'Login failed. Please try again.';

      if (e.response?.data != null) {
        try {
          final errorResponse = ErrorResponseModel.fromJson(e.response!.data);
          errorMessage = errorResponse.firstError ?? errorResponse.message;
        } catch (_) {
          errorMessage = e.message ?? errorMessage;
        }
      } else if (e.error is String) {
        errorMessage = e.error as String;
      }

      state = state.copyWith(
        isLoading: false,
        error: errorMessage,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'An unexpected error occurred. Please try again.',
      );
    }
  }

  /// Refresh user data from API
  Future<void> refreshUser() async {
    try {
      final response = await _apiClient.getUser();
      final userData = response.data['data'] as Map<String, dynamic>;
      final user = UserModel.fromJson(userData);

      // Update stored user data
      await _storage.saveUserData(user.toJsonString());

      // Update state
      state = state.copyWith(user: user);
    } catch (e) {
      print('Error refreshing user: $e');
      // If refresh fails, logout user
      await logout();
    }
  }

  /// Logout user
  Future<void> logout() async {
    state = state.copyWith(isLoading: true);

    try {
      // Call logout API to revoke token
      await _apiClient.logout();
    } catch (e) {
      print('Error during logout API call: $e');
    } finally {
      // Clear local storage regardless of API call result
      await _storage.clearAll();

      // Reset state
      state = const AuthState();
    }
  }

  /// Clear error message
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Auth provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier();
});
