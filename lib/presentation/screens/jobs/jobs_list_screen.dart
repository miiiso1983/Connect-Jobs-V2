import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/models/job_model.dart';
import '../../../core/constants/app_constants.dart';
import '../../bloc/jobs/jobs_bloc.dart';
import '../../bloc/jobs/jobs_event.dart';
import '../../bloc/jobs/jobs_state.dart';
import '../../../presentation/widgets/job_card.dart';
import '../../../presentation/widgets/search_bar_widget.dart';
import '../../../presentation/widgets/filter_bottom_sheet.dart';
import '../../../presentation/widgets/sort_bottom_sheet.dart';

class JobsListScreen extends StatefulWidget {
  const JobsListScreen({Key? key}) : super(key: key);

  @override
  State<JobsListScreen> createState() => _JobsListScreenState();
}

class _JobsListScreenState extends State<JobsListScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    // Load jobs when screen initializes
    context.read<JobsBloc>().add(JobsLoadRequested());
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_isBottom) {
      context.read<JobsBloc>().add(JobsLoadMoreRequested());
    }
  }

  bool get _isBottom {
    if (!_scrollController.hasClients) return false;
    final maxScroll = _scrollController.position.maxScrollExtent;
    final currentScroll = _scrollController.offset;
    return currentScroll >= (maxScroll * 0.9);
  }

  void _onSearchChanged(String query) {
    context.read<JobsBloc>().add(JobsSearchRequested(query: query));
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => FilterBottomSheet(
        onApplyFilters: (province, speciality, districts, specialities) {
          context.read<JobsBloc>().add(JobsFilterRequested(
            province: province,
            speciality: speciality,
            districts: districts,
            specialities: specialities,
          ));
        },
      ),
    );
  }

  void _showSortBottomSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => SortBottomSheet(
        onApplySort: (sortBy, sortOrder) {
          context.read<JobsBloc>().add(JobsSortRequested(
            sortBy: sortBy,
            sortOrder: sortOrder,
          ));
        },
      ),
    );
  }

  void _onRefresh() {
    context.read<JobsBloc>().add(JobsRefreshRequested());
  }

  void _clearFilters() {
    _searchController.clear();
    context.read<JobsBloc>().add(JobsClearFiltersRequested());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: Column(
        children: [
          // Search and Filter Bar
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.1),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              children: [
                SearchBarWidget(
                  controller: _searchController,
                  onChanged: _onSearchChanged,
                  hintText: 'ابحث عن وظيفة...',
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _showFilterBottomSheet,
                        icon: const Icon(Icons.filter_list),
                        label: const Text('فلترة'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _showSortBottomSheet,
                        icon: const Icon(Icons.sort),
                        label: const Text('ترتيب'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Jobs List
          Expanded(
            child: BlocBuilder<JobsBloc, JobsState>(
              builder: (context, state) {
                if (state is JobsLoading) {
                  return const Center(
                    child: CircularProgressIndicator(),
                  );
                }

                if (state is JobsError) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.error_outline,
                          size: 64,
                          color: Colors.red[300],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          state.message,
                          style: const TextStyle(
                            fontSize: 16,
                            color: Colors.red,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _onRefresh,
                          child: const Text('إعادة المحاولة'),
                        ),
                      ],
                    ),
                  );
                }

                if (state is JobsEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.work_off,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          state.hasFilters
                              ? 'لا توجد وظائف تطابق البحث'
                              : 'لا توجد وظائف متاحة حالياً',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                          textAlign: TextAlign.center,
                        ),
                        if (state.hasFilters) ...[
                          const SizedBox(height: 16),
                          ElevatedButton(
                            onPressed: _clearFilters,
                            child: const Text('مسح الفلاتر'),
                          ),
                        ],
                      ],
                    ),
                  );
                }

                if (state is JobsLoaded || state is JobsLoadingMore) {
                  final jobs = state is JobsLoaded 
                      ? state.jobs 
                      : (state as JobsLoadingMore).currentJobs;
                  
                  final hasFilters = state is JobsLoaded 
                      ? state.hasFilters 
                      : false;

                  return RefreshIndicator(
                    onRefresh: () async => _onRefresh(),
                    child: Column(
                      children: [
                        // Filter indicator
                        if (hasFilters)
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 8,
                            ),
                            color: Colors.blue[50],
                            child: Row(
                              children: [
                                Icon(
                                  Icons.filter_list,
                                  size: 16,
                                  color: Colors.blue[700],
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'تم تطبيق فلاتر البحث',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.blue[700],
                                  ),
                                ),
                                const Spacer(),
                                GestureDetector(
                                  onTap: _clearFilters,
                                  child: Text(
                                    'مسح الكل',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.blue[700],
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),

                        // Jobs count
                        if (state is JobsLoaded)
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(16),
                            child: Text(
                              'تم العثور على ${state.totalJobs} وظيفة',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.grey[600],
                              ),
                            ),
                          ),

                        // Jobs list
                        Expanded(
                          child: ListView.builder(
                            controller: _scrollController,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            itemCount: jobs.length + 
                                (state is JobsLoadingMore ? 1 : 0),
                            itemBuilder: (context, index) {
                              if (index >= jobs.length) {
                                return const Padding(
                                  padding: EdgeInsets.all(16),
                                  child: Center(
                                    child: CircularProgressIndicator(),
                                  ),
                                );
                              }

                              final job = jobs[index];
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 12),
                                child: JobCard(
                                  job: job,
                                  onTap: () => _navigateToJobDetails(job),
                                ),
                              );
                            },
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return const SizedBox.shrink();
              },
            ),
          ),
        ],
      ),
    );
  }

  void _navigateToJobDetails(Job job) {
    Navigator.pushNamed(
      context,
      '/job-details',
      arguments: job,
    );
  }
}
