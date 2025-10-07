import 'package:flutter/material.dart';

class SearchBarWidget extends StatefulWidget {
  final TextEditingController controller;
  final Function(String) onChanged;
  final String hintText;
  final bool enabled;

  const SearchBarWidget({
    Key? key,
    required this.controller,
    required this.onChanged,
    this.hintText = 'البحث...',
    this.enabled = true,
  }) : super(key: key);

  @override
  State<SearchBarWidget> createState() => _SearchBarWidgetState();
}

class _SearchBarWidgetState extends State<SearchBarWidget> {
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    widget.controller.addListener(_onTextChanged);
  }

  @override
  void dispose() {
    widget.controller.removeListener(_onTextChanged);
    super.dispose();
  }

  void _onTextChanged() {
    final hasText = widget.controller.text.isNotEmpty;
    if (hasText != _isSearching) {
      setState(() {
        _isSearching = hasText;
      });
    }
  }

  void _clearSearch() {
    widget.controller.clear();
    widget.onChanged('');
    FocusScope.of(context).unfocus();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.grey[300]!,
          width: 1,
        ),
      ),
      child: TextField(
        controller: widget.controller,
        enabled: widget.enabled,
        textDirection: TextDirection.rtl,
        style: const TextStyle(
          fontSize: 16,
          color: Colors.black87,
        ),
        decoration: InputDecoration(
          hintText: widget.hintText,
          hintStyle: TextStyle(
            color: Colors.grey[500],
            fontSize: 16,
          ),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 12,
          ),
          prefixIcon: _isSearching
              ? IconButton(
                  icon: const Icon(
                    Icons.clear,
                    color: Colors.grey,
                  ),
                  onPressed: _clearSearch,
                )
              : null,
          suffixIcon: const Icon(
            Icons.search,
            color: Colors.grey,
          ),
        ),
        onChanged: widget.onChanged,
        textInputAction: TextInputAction.search,
        onSubmitted: widget.onChanged,
      ),
    );
  }
}
