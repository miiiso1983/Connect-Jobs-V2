// Stub file - This is not the active implementation
// The main Flutter project is in connect_jobs_mobile/

abstract class JobsEvent {}

class JobsLoadRequested extends JobsEvent {}

class JobsLoadMoreRequested extends JobsEvent {}

class JobsSearchRequested extends JobsEvent {
  final String query;
  JobsSearchRequested({required this.query});
}

class JobsFilterRequested extends JobsEvent {
  final String? province;
  final String? speciality;
  final List<String>? districts;
  final List<String>? specialities;
  
  JobsFilterRequested({
    this.province,
    this.speciality,
    this.districts,
    this.specialities,
  });
}

class JobsSortRequested extends JobsEvent {
  final String sortBy;
  final String sortOrder;
  
  JobsSortRequested({required this.sortBy, required this.sortOrder});
}

class JobsRefreshRequested extends JobsEvent {}

class JobsClearFiltersRequested extends JobsEvent {}
