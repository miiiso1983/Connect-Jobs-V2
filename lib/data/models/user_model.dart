import 'package:json_annotation/json_annotation.dart';

part 'user_model.g.dart';

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

  // Helper methods
  bool get isCompany => role == 'company';
  bool get isJobSeeker => role == 'jobseeker';
  bool get isAdmin => role == 'admin';
  bool get isActive => status == 'active';
}

@JsonSerializable()
class LoginResponse {
  final User user;
  final String token;
  @JsonKey(name: 'token_type')
  final String tokenType;
  @JsonKey(name: 'expires_in')
  final int expiresIn;

  LoginResponse({
    required this.user,
    required this.token,
    required this.tokenType,
    required this.expiresIn,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) => _$LoginResponseFromJson(json);
  Map<String, dynamic> toJson() => _$LoginResponseToJson(this);
}
