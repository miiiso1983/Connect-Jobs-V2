import '../../../data/models/user_model.dart';

abstract class AuthState {}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class AuthAuthenticated extends AuthState {
  final User user;
  final String token;

  AuthAuthenticated({required this.user, required this.token});
}

class AuthUnauthenticated extends AuthState {}

class AuthError extends AuthState {
  final String message;
  final int? statusCode;

  AuthError({required this.message, this.statusCode});
}
