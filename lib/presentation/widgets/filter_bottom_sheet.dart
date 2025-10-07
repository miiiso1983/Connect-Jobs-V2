import 'package:flutter/material.dart';

class FilterBottomSheet extends StatefulWidget {
  final Function(String?, String?, List<String>?, List<String>?) onApplyFilters;

  const FilterBottomSheet({
    Key? key,
    required this.onApplyFilters,
  }) : super(key: key);

  @override
  State<FilterBottomSheet> createState() => _FilterBottomSheetState();
}

class _FilterBottomSheetState extends State<FilterBottomSheet> {
  String? _selectedProvince;
  String? _selectedSpeciality;
  List<String> _selectedDistricts = [];
  List<String> _selectedSpecialities = [];

  // Mock data - في التطبيق الحقيقي ستأتي من API
  final List<String> _provinces = [
    'بغداد',
    'البصرة',
    'الموصل',
    'أربيل',
    'النجف',
    'كربلاء',
    'الحلة',
    'الناصرية',
    'الديوانية',
    'السليمانية',
    'الأنبار',
    'صلاح الدين',
    'ديالى',
    'كركوك',
    'المثنى',
    'واسط',
    'ميسان',
    'دهوك',
  ];

  final List<String> _specialities = [
    'General Practitioner',
    'Internal Medicine',
    'Pediatrics',
    'Cardiologist',
    'Dermatologist',
    'Neurologist',
    'Orthopedics',
    'Ophthalmologist',
    'ENT',
    'Dentist',
    'Nurses',
    'Pharmacist',
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(20),
        ),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Handle
          Container(
            margin: const EdgeInsets.only(top: 12),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'فلترة الوظائف',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                TextButton(
                  onPressed: _clearFilters,
                  child: const Text('مسح الكل'),
                ),
              ],
            ),
          ),

          // Filters Content
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Province Filter
                  _buildSectionTitle('المحافظة'),
                  _buildDropdown(
                    value: _selectedProvince,
                    items: _provinces,
                    hint: 'اختر المحافظة',
                    onChanged: (value) {
                      setState(() {
                        _selectedProvince = value;
                      });
                    },
                  ),

                  const SizedBox(height: 24),

                  // Speciality Filter
                  _buildSectionTitle('التخصص'),
                  _buildDropdown(
                    value: _selectedSpeciality,
                    items: _specialities,
                    hint: 'اختر التخصص',
                    onChanged: (value) {
                      setState(() {
                        _selectedSpeciality = value;
                      });
                    },
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),
          ),

          // Action Buttons
          Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('إلغاء'),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _applyFilters,
                    child: const Text('تطبيق'),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: Colors.black87,
        ),
      ),
    );
  }

  Widget _buildDropdown({
    required String? value,
    required List<String> items,
    required String hint,
    required Function(String?) onChanged,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey[300]!),
        borderRadius: BorderRadius.circular(8),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          hint: Text(
            hint,
            style: TextStyle(color: Colors.grey[600]),
          ),
          isExpanded: true,
          items: items.map((String item) {
            return DropdownMenuItem<String>(
              value: item,
              child: Text(item),
            );
          }).toList(),
          onChanged: onChanged,
        ),
      ),
    );
  }

  void _clearFilters() {
    setState(() {
      _selectedProvince = null;
      _selectedSpeciality = null;
      _selectedDistricts.clear();
      _selectedSpecialities.clear();
    });
  }

  void _applyFilters() {
    widget.onApplyFilters(
      _selectedProvince,
      _selectedSpeciality,
      _selectedDistricts.isEmpty ? null : _selectedDistricts,
      _selectedSpecialities.isEmpty ? null : _selectedSpecialities,
    );
    Navigator.pop(context);
  }
}
