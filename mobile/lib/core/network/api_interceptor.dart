import 'package:dio/dio.dart';
import '../storage/secure_storage_service.dart';

/// Dio interceptor for handling authentication tokens and error responses
class ApiInterceptor extends Interceptor {
  final SecureStorageService _storage = SecureStorageService();

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    // Get token from secure storage
    final token = await _storage.getToken();

    // Add Bearer token to headers if available
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    // Add Accept and Content-Type headers
    options.headers['Accept'] = 'application/json';
    options.headers['Content-Type'] = 'application/json';

    // Log request in debug mode
    print('🌐 REQUEST[${options.method}] => ${options.uri}');
    if (options.data != null) {
      print('📤 DATA: ${options.data}');
    }

    super.onRequest(options, handler);
  }

  @override
  void onResponse(
    Response response,
    ResponseInterceptorHandler handler,
  ) {
    // Log response in debug mode
    print('✅ RESPONSE[${response.statusCode}] => ${response.requestOptions.uri}');

    super.onResponse(response, handler);
  }

  @override
  void onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) {
    // Log error in debug mode
    print('❌ ERROR[${err.response?.statusCode}] => ${err.requestOptions.uri}');
    print('💥 ERROR MESSAGE: ${err.message}');

    if (err.response?.data != null) {
      print('📥 ERROR DATA: ${err.response?.data}');
    }

    // Handle specific error cases
    switch (err.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        handler.reject(
          DioException(
            requestOptions: err.requestOptions,
            error: 'Connection timeout. Please check your internet connection.',
            type: err.type,
          ),
        );
        break;

      case DioExceptionType.badResponse:
        final statusCode = err.response?.statusCode;

        if (statusCode == 401) {
          // Unauthorized - token expired or invalid
          _handleUnauthorized();
          handler.reject(
            DioException(
              requestOptions: err.requestOptions,
              error: 'Session expired. Please login again.',
              response: err.response,
              type: err.type,
            ),
          );
        } else if (statusCode == 422) {
          // Validation error - pass through original error
          handler.reject(err);
        } else if (statusCode == 429) {
          // Too many requests
          handler.reject(
            DioException(
              requestOptions: err.requestOptions,
              error: 'Too many requests. Please try again later.',
              response: err.response,
              type: err.type,
            ),
          );
        } else if (statusCode != null && statusCode >= 500) {
          // Server error
          handler.reject(
            DioException(
              requestOptions: err.requestOptions,
              error: 'Server error. Please try again later.',
              response: err.response,
              type: err.type,
            ),
          );
        } else {
          handler.reject(err);
        }
        break;

      case DioExceptionType.connectionError:
        handler.reject(
          DioException(
            requestOptions: err.requestOptions,
            error: 'No internet connection. Please check your network.',
            type: err.type,
          ),
        );
        break;

      default:
        handler.reject(err);
    }
  }

  /// Handle unauthorized error by clearing stored auth data
  Future<void> _handleUnauthorized() async {
    await _storage.clearAll();
  }
}
