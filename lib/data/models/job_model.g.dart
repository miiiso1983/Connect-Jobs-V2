// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'job_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Job _$JobFromJson(Map<String, dynamic> json) => Job(
  id: (json['id'] as num).toInt(),
  companyId: (json['company_id'] as num).toInt(),
  title: json['title'] as String,
  speciality: json['speciality'] as String?,
  specialities: (json['specialities'] as List<dynamic>?)
      ?.map((e) => e as String)
      .toList(),
  description: json['description'] as String,
  requirements: json['requirements'] as String,
  province: json['province'] as String?,
  districts: (json['districts'] as List<dynamic>?)
      ?.map((e) => e as String)
      .toList(),
  status: json['status'] as String,
  approvedByAdmin: json['approved_by_admin'] as bool,
  company: json['company'] == null
      ? null
      : Company.fromJson(json['company'] as Map<String, dynamic>),
  applicationsCount: (json['applications_count'] as num?)?.toInt(),
  hasApplied: json['has_applied'] as bool?,
);

Map<String, dynamic> _$JobToJson(Job instance) => <String, dynamic>{
  'id': instance.id,
  'company_id': instance.companyId,
  'title': instance.title,
  'speciality': instance.speciality,
  'specialities': instance.specialities,
  'description': instance.description,
  'requirements': instance.requirements,
  'province': instance.province,
  'districts': instance.districts,
  'status': instance.status,
  'approved_by_admin': instance.approvedByAdmin,
  'company': instance.company,
  'applications_count': instance.applicationsCount,
  'has_applied': instance.hasApplied,
};

Company _$CompanyFromJson(Map<String, dynamic> json) => Company(
  id: (json['id'] as num).toInt(),
  userId: (json['user_id'] as num).toInt(),
  companyName: json['company_name'] as String,
  scientificOfficeName: json['scientific_office_name'] as String?,
  companyJobTitle: json['company_job_title'] as String?,
  mobileNumber: json['mobile_number'] as String?,
  province: json['province'] as String?,
  industry: json['industry'] as String?,
  subscriptionPlan: json['subscription_plan'] as String,
  status: json['status'] as String,
  user: json['user'] == null
      ? null
      : User.fromJson(json['user'] as Map<String, dynamic>),
);

Map<String, dynamic> _$CompanyToJson(Company instance) => <String, dynamic>{
  'id': instance.id,
  'user_id': instance.userId,
  'company_name': instance.companyName,
  'scientific_office_name': instance.scientificOfficeName,
  'company_job_title': instance.companyJobTitle,
  'mobile_number': instance.mobileNumber,
  'province': instance.province,
  'industry': instance.industry,
  'subscription_plan': instance.subscriptionPlan,
  'status': instance.status,
  'user': instance.user,
};

User _$UserFromJson(Map<String, dynamic> json) => User(
  id: (json['id'] as num).toInt(),
  name: json['name'] as String,
  email: json['email'] as String,
  role: json['role'] as String,
  status: json['status'] as String,
  createdAt: json['created_at'] as String?,
);

Map<String, dynamic> _$UserToJson(User instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'email': instance.email,
  'role': instance.role,
  'status': instance.status,
  'created_at': instance.createdAt,
};

JobsResponse _$JobsResponseFromJson(Map<String, dynamic> json) => JobsResponse(
  currentPage: (json['current_page'] as num).toInt(),
  data: (json['data'] as List<dynamic>)
      .map((e) => Job.fromJson(e as Map<String, dynamic>))
      .toList(),
  lastPage: (json['last_page'] as num).toInt(),
  nextPageUrl: json['next_page_url'] as String?,
  perPage: (json['per_page'] as num).toInt(),
  prevPageUrl: json['prev_page_url'] as String?,
  total: (json['total'] as num).toInt(),
);

Map<String, dynamic> _$JobsResponseToJson(JobsResponse instance) =>
    <String, dynamic>{
      'current_page': instance.currentPage,
      'data': instance.data,
      'last_page': instance.lastPage,
      'next_page_url': instance.nextPageUrl,
      'per_page': instance.perPage,
      'prev_page_url': instance.prevPageUrl,
      'total': instance.total,
    };

JobDetailsResponse _$JobDetailsResponseFromJson(Map<String, dynamic> json) =>
    JobDetailsResponse(
      job: Job.fromJson(json['job'] as Map<String, dynamic>),
      hasApplied: json['has_applied'] as bool,
    );

Map<String, dynamic> _$JobDetailsResponseToJson(JobDetailsResponse instance) =>
    <String, dynamic>{'job': instance.job, 'has_applied': instance.hasApplied};
