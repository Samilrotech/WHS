import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';

/// Secure storage service for sensitive data like authentication tokens
/// Uses SharedPreferences on web, FlutterSecureStorage on mobile
class SecureStorageService {
  static final SecureStorageService _instance = SecureStorageService._internal();
  factory SecureStorageService() => _instance;
  SecureStorageService._internal();

  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
    ),
    iOptions: IOSOptions(
      accessibility: KeychainAccessibility.first_unlock,
    ),
  );

  late SharedPreferences _prefs;

  /// Initialize shared preferences
  Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // ==================== Token Management ====================

  /// Save authentication token securely
  Future<void> saveToken(String token) async {
    if (kIsWeb) {
      // Use SharedPreferences on web
      await _prefs.setString(ApiConstants.authTokenKey, token);
    } else {
      // Use FlutterSecureStorage on mobile
      await _secureStorage.write(
        key: ApiConstants.authTokenKey,
        value: token,
      );
    }
  }

  /// Get authentication token
  Future<String?> getToken() async {
    if (kIsWeb) {
      // Use SharedPreferences on web
      return _prefs.getString(ApiConstants.authTokenKey);
    } else {
      // Use FlutterSecureStorage on mobile
      return await _secureStorage.read(key: ApiConstants.authTokenKey);
    }
  }

  /// Delete authentication token
  Future<void> deleteToken() async {
    if (kIsWeb) {
      // Use SharedPreferences on web
      await _prefs.remove(ApiConstants.authTokenKey);
    } else {
      // Use FlutterSecureStorage on mobile
      await _secureStorage.delete(key: ApiConstants.authTokenKey);
    }
  }

  /// Check if user is authenticated (has token)
  Future<bool> isAuthenticated() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // ==================== User Data Management ====================

  /// Save user data as JSON string
  Future<void> saveUserData(String userData) async {
    if (kIsWeb) {
      // Use SharedPreferences on web
      await _prefs.setString(ApiConstants.userDataKey, userData);
    } else {
      // Use FlutterSecureStorage on mobile
      await _secureStorage.write(
        key: ApiConstants.userDataKey,
        value: userData,
      );
    }
  }

  /// Get user data
  Future<String?> getUserData() async {
    if (kIsWeb) {
      // Use SharedPreferences on web
      return _prefs.getString(ApiConstants.userDataKey);
    } else {
      // Use FlutterSecureStorage on mobile
      return await _secureStorage.read(key: ApiConstants.userDataKey);
    }
  }

  /// Delete user data
  Future<void> deleteUserData() async {
    if (kIsWeb) {
      // Use SharedPreferences on web
      await _prefs.remove(ApiConstants.userDataKey);
    } else {
      // Use FlutterSecureStorage on mobile
      await _secureStorage.delete(key: ApiConstants.userDataKey);
    }
  }

  // ==================== Preferences Management ====================

  /// Save remember me preference
  Future<void> setRememberMe(bool remember) async {
    await _prefs.setBool(ApiConstants.rememberMeKey, remember);
  }

  /// Get remember me preference
  bool getRememberMe() {
    return _prefs.getBool(ApiConstants.rememberMeKey) ?? false;
  }

  // ==================== Clear All Data ====================

  /// Clear all stored data (logout)
  Future<void> clearAll() async {
    if (kIsWeb) {
      // Clear SharedPreferences on web
      await _prefs.remove(ApiConstants.authTokenKey);
      await _prefs.remove(ApiConstants.userDataKey);
      await _prefs.remove(ApiConstants.rememberMeKey);
    } else {
      // Clear FlutterSecureStorage on mobile
      await _secureStorage.deleteAll();
      await _prefs.clear();
    }
  }
}
