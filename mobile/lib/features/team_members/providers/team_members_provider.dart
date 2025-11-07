import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../models/team_member_model.dart';

/// Team members state
class TeamMembersState {
  final List<TeamMemberModel> members;
  final PaginationMeta? meta;
  final bool isLoading;
  final bool isLoadingMore;
  final String? error;
  final String? searchQuery;

  const TeamMembersState({
    this.members = const [],
    this.meta,
    this.isLoading = false,
    this.isLoadingMore = false,
    this.error,
    this.searchQuery,
  });

  TeamMembersState copyWith({
    List<TeamMemberModel>? members,
    PaginationMeta? meta,
    bool? isLoading,
    bool? isLoadingMore,
    String? error,
    String? searchQuery,
  }) {
    return TeamMembersState(
      members: members ?? this.members,
      meta: meta ?? this.meta,
      isLoading: isLoading ?? this.isLoading,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
      error: error,
      searchQuery: searchQuery ?? this.searchQuery,
    );
  }

  bool get hasMore => meta?.hasMore ?? false;
}

/// Team members provider
class TeamMembersNotifier extends StateNotifier<TeamMembersState> {
  final ApiClient _apiClient = ApiClient();

  TeamMembersNotifier() : super(const TeamMembersState()) {
    loadTeamMembers();
  }

  /// Load team members (first page)
  Future<void> loadTeamMembers({String? search}) async {
    state = state.copyWith(
      isLoading: true,
      error: null,
      searchQuery: search,
    );

    try {
      final response = await _apiClient.getTeamMembers(
        page: 1,
        perPage: 20,
        search: search,
      );

      final teamMembersResponse = TeamMembersResponse.fromJson(response.data);

      state = state.copyWith(
        members: teamMembersResponse.data,
        meta: teamMembersResponse.meta,
        isLoading: false,
        error: null,
      );
    } on DioException catch (e) {
      String errorMessage = 'Failed to load team members';

      if (e.error is String) {
        errorMessage = e.error as String;
      } else if (e.message != null) {
        errorMessage = e.message!;
      }

      state = state.copyWith(
        isLoading: false,
        error: errorMessage,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'An unexpected error occurred',
      );
    }
  }

  /// Load more team members (next page)
  Future<void> loadMore() async {
    if (!state.hasMore || state.isLoadingMore) return;

    state = state.copyWith(isLoadingMore: true);

    try {
      final nextPage = (state.meta?.currentPage ?? 0) + 1;

      final response = await _apiClient.getTeamMembers(
        page: nextPage,
        perPage: 20,
        search: state.searchQuery,
      );

      final teamMembersResponse = TeamMembersResponse.fromJson(response.data);

      state = state.copyWith(
        members: [...state.members, ...teamMembersResponse.data],
        meta: teamMembersResponse.meta,
        isLoadingMore: false,
      );
    } catch (e) {
      state = state.copyWith(isLoadingMore: false);
    }
  }

  /// Search team members
  Future<void> search(String query) async {
    await loadTeamMembers(search: query.trim());
  }

  /// Refresh team members
  Future<void> refresh() async {
    await loadTeamMembers(search: state.searchQuery);
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Team members provider
final teamMembersProvider =
    StateNotifierProvider<TeamMembersNotifier, TeamMembersState>((ref) {
  return TeamMembersNotifier();
});

/// Single team member detail provider
final teamMemberDetailProvider =
    FutureProvider.family<TeamMemberModel, String>((ref, id) async {
  final apiClient = ApiClient();
  final response = await apiClient.getTeamMember(id);
  return TeamMemberModel.fromJson(response.data['data']);
});
