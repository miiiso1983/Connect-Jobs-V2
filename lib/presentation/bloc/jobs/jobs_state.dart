// Stub file - This is not the active implementation
// The main Flutter project is in connect_jobs_mobile/

import '../../../data/models/job_model.dart';

abstract class JobsState {}

class JobsInitial extends JobsState {}

class JobsLoading extends JobsState {}

class JobsLoaded extends JobsState {
  final List<Job> jobs;
  final bool hasFilters;
  final int totalCount;
  final int totalJobs;

  JobsLoaded({
    required this.jobs,
    this.hasFilters = false,
    this.totalCount = 0,
    int? totalJobs,
  }) : totalJobs = totalJobs ?? jobs.length;
}

class JobsLoadingMore extends JobsState {
  final List<Job> currentJobs;
  
  JobsLoadingMore({required this.currentJobs});
}

class JobsEmpty extends JobsState {
  final bool hasFilters;

  JobsEmpty({this.hasFilters = false});
}

class JobsError extends JobsState {
  final String message;
  
  JobsError({required this.message});
}
