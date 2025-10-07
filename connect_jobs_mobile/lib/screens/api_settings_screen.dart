import 'package:flutter/material.dart';
import '../utils/runtime_config.dart';

class ApiSettingsScreen extends StatefulWidget {
  const ApiSettingsScreen({super.key});

  @override
  State<ApiSettingsScreen> createState() => _ApiSettingsScreenState();
}

class _ApiSettingsScreenState extends State<ApiSettingsScreen> {
  late final TextEditingController _baseUrlCtrl;
  late final TextEditingController _loginPathCtrl;
  late final TextEditingController _regSeekerPathCtrl;
  late final TextEditingController _regCompanyPathCtrl;

  @override
  void initState() {
    super.initState();
    _baseUrlCtrl = TextEditingController(text: RuntimeConfig.baseUrl);
    _loginPathCtrl = TextEditingController(text: RuntimeConfig.authLoginPath);
    _regSeekerPathCtrl = TextEditingController(text: RuntimeConfig.registerJobSeekerPath);
    _regCompanyPathCtrl = TextEditingController(text: RuntimeConfig.registerCompanyPath);
  }

  @override
  void dispose() {
    _baseUrlCtrl.dispose();
    _loginPathCtrl.dispose();
    _regSeekerPathCtrl.dispose();
    _regCompanyPathCtrl.dispose();
    super.dispose();
  }

  void _save() {
    RuntimeConfig.apply(
      newBaseUrl: _baseUrlCtrl.text,
      newAuthLoginPath: _loginPathCtrl.text,
      newRegisterJobSeekerPath: _regSeekerPathCtrl.text,
      newRegisterCompanyPath: _regCompanyPathCtrl.text,
    );
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تم حفظ إعدادات الـ API (لجلسة التشغيل الحالية).')),
    );
    Navigator.pop(context);
  }

  void _reset() {
    RuntimeConfig.resetToDefaults();
    setState(() {
      _baseUrlCtrl.text = RuntimeConfig.baseUrl;
      _loginPathCtrl.text = RuntimeConfig.authLoginPath;
      _regSeekerPathCtrl.text = RuntimeConfig.registerJobSeekerPath;
      _regCompanyPathCtrl.text = RuntimeConfig.registerCompanyPath;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تمت استعادة القيم الافتراضية.')),
    );
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: const Text('إعدادات API'),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: ListView(
          children: [
            TextField(
              controller: _baseUrlCtrl,
              decoration: const InputDecoration(
                labelText: 'Base URL (مثال: https://api.example.com/api/v1/) ',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _loginPathCtrl,
              decoration: const InputDecoration(
                labelText: 'Login Path (مثال: auth/login)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _regSeekerPathCtrl,
              decoration: const InputDecoration(
                labelText: 'Register Jobseeker Path (مثال: auth/register/jobseeker)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _regCompanyPathCtrl,
              decoration: const InputDecoration(
                labelText: 'Register Company Path (مثال: auth/register/company)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: FilledButton(
                    onPressed: _save,
                    style: FilledButton.styleFrom(
                      backgroundColor: scheme.primary,
                      foregroundColor: scheme.onPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: const Text('حفظ'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton(
                    onPressed: _reset,
                    child: const Text('إرجاع الافتراضي'),
                  ),
                ),
              ],
            )
          ],
        ),
      ),
    );
  }
}

