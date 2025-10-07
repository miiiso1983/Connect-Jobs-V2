import 'package:flutter/material.dart';

class AppTheme {
  // Convert brand HSL (h: 0-360, s/l: 0-100%) to a Flutter Color
  static Color hsl(num h, num sPct, num lPct) =>
      HSLColor.fromAHSL(1.0, h.toDouble(), sPct.toDouble() / 100.0, lPct.toDouble() / 100.0).toColor();

  // Brand tokens from www.connect-job.com (light theme ':root[data-theme=brand]')
  static final Color primary = hsl(222, 76, 21); // navy
  static const Color onPrimary = Colors.white;   // pc
  static final Color secondary = hsl(39, 57, 59); // gold
  static final Color tertiary = hsl(44, 72, 66); // accent

  static final Color background = hsl(0, 0, 100);      // b1
  static final Color surface = hsl(220, 20, 98);       // b2
  static final Color surfaceVariant = hsl(220, 14, 96); // b3
  static final Color onBackground = hsl(222, 43, 20);  // bc
  static final Color onSurface = onBackground;

  static ThemeData get light {
    final scheme = ColorScheme.fromSeed(
      brightness: Brightness.light,
      seedColor: primary,
      primary: primary,
      secondary: secondary,
      tertiary: tertiary,
      // onSecondary / onTertiary will be derived appropriately
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: scheme.surface,
      appBarTheme: AppBarTheme(
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
        elevation: 0,
        centerTitle: true,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: scheme.primary,
          foregroundColor: scheme.onPrimary,
          shape: const StadiumBorder(),
          textStyle: const TextStyle(fontWeight: FontWeight.w600),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: scheme.primary,
          side: BorderSide(color: scheme.primary),
          shape: const StadiumBorder(),
          textStyle: const TextStyle(fontWeight: FontWeight.w600),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surfaceVariant,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
      ),
    );
  }
}

