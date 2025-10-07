import 'package:flutter/material.dart';

class SortBottomSheet extends StatefulWidget {
  final Function(String, String) onApplySort;

  const SortBottomSheet({
    Key? key,
    required this.onApplySort,
  }) : super(key: key);

  @override
  State<SortBottomSheet> createState() => _SortBottomSheetState();
}

class _SortBottomSheetState extends State<SortBottomSheet> {
  String _selectedSortBy = 'created_at';
  String _selectedSortOrder = 'desc';

  final List<SortOption> _sortOptions = [
    SortOption(
      value: 'created_at',
      label: 'تاريخ النشر',
      icon: Icons.schedule,
    ),
    SortOption(
      value: 'title',
      label: 'اسم الوظيفة',
      icon: Icons.work,
    ),
    SortOption(
      value: 'province',
      label: 'المحافظة',
      icon: Icons.location_on,
    ),
    SortOption(
      value: 'speciality',
      label: 'التخصص',
      icon: Icons.category,
    ),
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
          const Padding(
            padding: EdgeInsets.all(20),
            child: Text(
              'ترتيب الوظائف',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),

          // Sort Options
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'ترتيب حسب:',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: Colors.black87,
                  ),
                ),
                const SizedBox(height: 16),
                
                // Sort By Options
                ..._sortOptions.map((option) => _buildSortOption(option)),

                const SizedBox(height: 24),

                // Sort Order
                const Text(
                  'نوع الترتيب:',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: Colors.black87,
                  ),
                ),
                const SizedBox(height: 16),

                Row(
                  children: [
                    Expanded(
                      child: _buildSortOrderOption(
                        value: 'desc',
                        label: 'تنازلي',
                        subtitle: 'الأحدث أولاً',
                        icon: Icons.arrow_downward,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _buildSortOrderOption(
                        value: 'asc',
                        label: 'تصاعدي',
                        subtitle: 'الأقدم أولاً',
                        icon: Icons.arrow_upward,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 32),
              ],
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
                    onPressed: _applySort,
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

  Widget _buildSortOption(SortOption option) {
    final isSelected = _selectedSortBy == option.value;
    
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedSortBy = option.value;
          });
        },
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            border: Border.all(
              color: isSelected ? Colors.blue : Colors.grey[300]!,
              width: isSelected ? 2 : 1,
            ),
            borderRadius: BorderRadius.circular(8),
            color: isSelected ? Colors.blue[50] : null,
          ),
          child: Row(
            children: [
              Icon(
                option.icon,
                color: isSelected ? Colors.blue[700] : Colors.grey[600],
                size: 20,
              ),
              const SizedBox(width: 12),
              Text(
                option.label,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                  color: isSelected ? Colors.blue[700] : Colors.black87,
                ),
              ),
              const Spacer(),
              if (isSelected)
                Icon(
                  Icons.check_circle,
                  color: Colors.blue[700],
                  size: 20,
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSortOrderOption({
    required String value,
    required String label,
    required String subtitle,
    required IconData icon,
  }) {
    final isSelected = _selectedSortOrder == value;
    
    return InkWell(
      onTap: () {
        setState(() {
          _selectedSortOrder = value;
        });
      },
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          border: Border.all(
            color: isSelected ? Colors.blue : Colors.grey[300]!,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(8),
          color: isSelected ? Colors.blue[50] : null,
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: isSelected ? Colors.blue[700] : Colors.grey[600],
              size: 24,
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                fontSize: 14,
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                color: isSelected ? Colors.blue[700] : Colors.black87,
              ),
            ),
            Text(
              subtitle,
              style: TextStyle(
                fontSize: 12,
                color: isSelected ? Colors.blue[600] : Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _applySort() {
    widget.onApplySort(_selectedSortBy, _selectedSortOrder);
    Navigator.pop(context);
  }
}

class SortOption {
  final String value;
  final String label;
  final IconData icon;

  SortOption({
    required this.value,
    required this.label,
    required this.icon,
  });
}
