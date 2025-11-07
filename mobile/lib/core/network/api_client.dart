import 'package:dio/dio.dart';
import '../constants/api_constants.dart';
import 'api_interceptor.dart';

/// Dio-based HTTP client for API communication
class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;
  ApiClient._internal();

  late Dio _dio;

  /// Initialize Dio with configuration
  void init() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.apiUrl,
        connectTimeout: ApiConstants.connectTimeout,
        receiveTimeout: ApiConstants.receiveTimeout,
        sendTimeout: ApiConstants.sendTimeout,
        headers: {
          'Accept': ApiConstants.acceptHeader,
          'Content-Type': ApiConstants.contentTypeHeader,
        },
      ),
    );

    // Add interceptor for token management and error handling
    _dio.interceptors.add(ApiInterceptor());

    // Add logging interceptor in debug mode
    _dio.interceptors.add(
      LogInterceptor(
        requestBody: true,
        responseBody: true,
        error: true,
        requestHeader: true,
        responseHeader: false,
      ),
    );
  }

  /// Get Dio instance
  Dio get dio => _dio;

  // ==================== Authentication Endpoints ====================

  /// Login with email and password
  /// Returns token and user data
  Future<Response> login({
    required String email,
    required String password,
    required String deviceName,
  }) async {
    return await _dio.post(
      ApiConstants.login,
      data: {
        'email': email,
        'password': password,
        'device_name': deviceName,
      },
    );
  }

  /// Get authenticated user data
  Future<Response> getUser() async {
    return await _dio.get(ApiConstants.user);
  }

  /// Logout and revoke current token
  Future<Response> logout() async {
    return await _dio.post(ApiConstants.logout);
  }

  /// Revoke specific token by ID
  Future<Response> revokeToken(String tokenId) async {
    return await _dio.delete('${ApiConstants.revokeToken}/$tokenId');
  }

  // ==================== Team Members Endpoints ====================

  /// Get team members list with pagination and search
  ///
  /// Parameters:
  /// - [page]: Page number (default: 1)
  /// - [perPage]: Items per page (default: 15)
  /// - [search]: Search query for name, email, or employee ID
  /// - [branchId]: Filter by branch ID
  /// - [status]: Filter by employment status
  /// - [sortBy]: Sort field (default: name)
  /// - [sortOrder]: Sort order (asc/desc, default: asc)
  Future<Response> getTeamMembers({
    int page = 1,
    int perPage = 15,
    String? search,
    String? branchId,
    String? status,
    String sortBy = 'name',
    String sortOrder = 'asc',
  }) async {
    return await _dio.get(
      ApiConstants.teamMembers,
      queryParameters: {
        'page': page,
        'per_page': perPage,
        if (search != null && search.isNotEmpty) 'search': search,
        if (branchId != null && branchId.isNotEmpty) 'branch_id': branchId,
        if (status != null && status.isNotEmpty) 'status': status,
        'sort_by': sortBy,
        'sort_order': sortOrder,
      },
    );
  }

  /// Get single team member by ID
  Future<Response> getTeamMember(String id) async {
    return await _dio.get('${ApiConstants.teamMembers}/$id');
  }

  // ==================== Generic HTTP Methods ====================

  /// Generic GET request
  Future<Response> get(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    return await _dio.get(
      path,
      queryParameters: queryParameters,
      options: options,
    );
  }

  /// Generic POST request
  Future<Response> post(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    return await _dio.post(
      path,
      data: data,
      queryParameters: queryParameters,
      options: options,
    );
  }

  /// Generic PUT request
  Future<Response> put(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    return await _dio.put(
      path,
      data: data,
      queryParameters: queryParameters,
      options: options,
    );
  }

  /// Generic DELETE request
  Future<Response> delete(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    return await _dio.delete(
      path,
      data: data,
      queryParameters: queryParameters,
      options: options,
    );
  }
}
