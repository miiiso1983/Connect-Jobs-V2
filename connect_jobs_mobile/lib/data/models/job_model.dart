import 'package:json_annotation/json_annotation.dart';

part 'job_model.g.dart';

@JsonSerializable()
class Job {
  final int id;
  @JsonKey(name: 'company_id')
  final int companyId;
  final String title;
  final String? speciality;
  final List<String>? specialities;
  final String description;
  final String requirements;
  final String? province;
  final List<String>? districts;
  final String status;
  @JsonKey(name: 'approved_by_admin')
  final bool approvedByAdmin;
  final Company? company;
  @JsonKey(name: 'applications_count')
  final int? applicationsCount;
  @JsonKey(name: 'has_applied')
  final bool? hasApplied;

  Job({
    required this.id,
    required this.companyId,
    required this.title,
    this.speciality,
    this.specialities,
    required this.description,
    required this.requirements,
    this.province,
    this.districts,
    required this.status,
    required this.approvedByAdmin,
    this.company,
    this.applicationsCount,
    this.hasApplied,
  });

  factory Job.fromJson(Map<String, dynamic> json) => _$JobFromJson(json);
  Map<String, dynamic> toJson() => _$JobToJson(this);

  // Helper getters
  bool get isActive => status == 'open';
  String get companyName => company?.companyName ?? 'غير محدد';
  DateTime? get createdAt => null; // Not available in current API
}

@JsonSerializable()
class Company {
  final int id;
  @JsonKey(name: 'user_id')
  final int userId;
  @JsonKey(name: 'company_name')
  final String companyName;
  @JsonKey(name: 'scientific_office_name')
  final String? scientificOfficeName;
  @JsonKey(name: 'company_job_title')
  final String? companyJobTitle;
  @JsonKey(name: 'mobile_number')
  final String? mobileNumber;
  final String? province;
  final String? industry;
  @JsonKey(name: 'subscription_plan')
  final String subscriptionPlan;
  final String status;
  final User? user;

  Company({
    required this.id,
    required this.userId,
    required this.companyName,
    this.scientificOfficeName,
    this.companyJobTitle,
    this.mobileNumber,
    this.province,
    this.industry,
    required this.subscriptionPlan,
    required this.status,
    this.user,
  });

  factory Company.fromJson(Map<String, dynamic> json) => _$CompanyFromJson(json);
  Map<String, dynamic> toJson() => _$CompanyToJson(this);
}

@JsonSerializable()
class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final String status;
  @JsonKey(name: 'created_at')
  final String? createdAt;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.status,
    this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
  Map<String, dynamic> toJson() => _$UserToJson(this);
}

@JsonSerializable()
class JobsResponse {
  @JsonKey(name: 'current_page')
  final int currentPage;
  final List<Job> data;
  @JsonKey(name: 'last_page')
  final int lastPage;
  @JsonKey(name: 'next_page_url')
  final String? nextPageUrl;
  @JsonKey(name: 'per_page')
  final int perPage;
  @JsonKey(name: 'prev_page_url')
  final String? prevPageUrl;
  final int total;

  JobsResponse({
    required this.currentPage,
    required this.data,
    required this.lastPage,
    this.nextPageUrl,
    required this.perPage,
    this.prevPageUrl,
    required this.total,
  });

  factory JobsResponse.fromJson(Map<String, dynamic> json) => _$JobsResponseFromJson(json);
  Map<String, dynamic> toJson() => _$JobsResponseToJson(this);

  // Helper getters
  bool get hasNextPage => nextPageUrl != null;
  bool get hasPrevPage => prevPageUrl != null;
  bool get isLastPage => currentPage >= lastPage;
  bool get isFirstPage => currentPage <= 1;
}

@JsonSerializable()
class JobDetailsResponse {
  final Job job;
  @JsonKey(name: 'has_applied')
  final bool hasApplied;

  JobDetailsResponse({
    required this.job,
    required this.hasApplied,
  });

  factory JobDetailsResponse.fromJson(Map<String, dynamic> json) => _$JobDetailsResponseFromJson(json);
  Map<String, dynamic> toJson() => _$JobDetailsResponseToJson(this);
}
