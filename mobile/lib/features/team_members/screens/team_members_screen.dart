import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/team_members_provider.dart';
import '../widgets/team_member_card.dart';
import 'team_member_detail_screen.dart';

class TeamMembersScreen extends ConsumerStatefulWidget {
  const TeamMembersScreen({super.key});

  @override
  ConsumerState<TeamMembersScreen> createState() => _TeamMembersScreenState();
}

class _TeamMembersScreenState extends ConsumerState<TeamMembersScreen> {
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent * 0.8) {
      ref.read(teamMembersProvider.notifier).loadMore();
    }
  }

  Future<void> _handleRefresh() async {
    await ref.read(teamMembersProvider.notifier).refresh();
  }

  void _handleSearch(String query) {
    ref.read(teamMembersProvider.notifier).search(query);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(teamMembersProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Team Members'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(60),
          child: Padding(
            padding: const EdgeInsets.all(8.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search by name, email, or employee ID',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _handleSearch('');
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide.none,
                ),
              ),
              onChanged: (value) {
                // Debounce search
                Future.delayed(const Duration(milliseconds: 500), () {
                  if (_searchController.text == value) {
                    _handleSearch(value);
                  }
                });
              },
            ),
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _handleRefresh,
        child: _buildBody(state),
      ),
    );
  }

  Widget _buildBody(TeamMembersState state) {
    if (state.isLoading) {
      return const Center(
        child: CircularProgressIndicator(),
      );
    }

    if (state.error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline,
              size: 64,
              color: Colors.red,
            ),
            const SizedBox(height: 16),
            Text(
              state.error!,
              style: const TextStyle(fontSize: 16),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: () =>
                  ref.read(teamMembersProvider.notifier).refresh(),
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ],
        ),
      );
    }

    if (state.members.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.people_outline,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text(
              state.searchQuery != null && state.searchQuery!.isNotEmpty
                  ? 'No team members found'
                  : 'No team members available',
              style: const TextStyle(fontSize: 16, color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(8),
      itemCount: state.members.length + (state.hasMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index >= state.members.length) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(16.0),
              child: CircularProgressIndicator(),
            ),
          );
        }

        final member = state.members[index];
        return TeamMemberCard(
          member: member,
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => TeamMemberDetailScreen(
                  memberId: member.id,
                ),
              ),
            );
          },
        );
      },
    );
  }
}
