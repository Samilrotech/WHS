/// API Configuration Constants
class ApiConstants {
  // Base URL - Update this for production deployment
  static const String baseUrl = 'http://127.0.0.1:8002/api/v1/mobile';

  // Production URL (Hostinger)
  static const String productionUrl = 'https://whs.rotechrural.com.au/api/v1/mobile';

  // Determine which URL to use based on build mode
  static String get apiUrl {
    // Use production URL for deployed app
    return productionUrl;
  }

  // API Endpoints
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String user = '/auth/user';
  static const String revokeToken = '/auth/tokens';
  static const String teamMembers = '/team-members';

  // Request timeouts
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
  static const Duration sendTimeout = Duration(seconds: 30);

  // Storage keys
  static const String authTokenKey = 'auth_token';
  static const String userDataKey = 'user_data';
  static const String rememberMeKey = 'remember_me';

  // Headers
  static const String acceptHeader = 'application/json';
  static const String contentTypeHeader = 'application/json';
}
