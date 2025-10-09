import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class AdminWebViewScreen extends StatefulWidget {
  final String title;
  final String url;
  final String? postLoadJs; // optional JS to run after page finished
  const AdminWebViewScreen({super.key, required this.title, required this.url, this.postLoadJs});

  @override
  State<AdminWebViewScreen> createState() => _AdminWebViewScreenState();
}

class _AdminWebViewScreenState extends State<AdminWebViewScreen> {
  late final WebViewController _controller;
  bool _isLoading = true;

  // Enforce a canonical host/protocol to avoid cookie loss across www/http variants
  late final Uri _startUri = Uri.parse(widget.url);
  late final String _canonicalHost = _startUri.host.startsWith('www.') ? _startUri.host : 'www.${_startUri.host}';

  Uri _toCanonical(Uri uri) {
    // Force https and canonical host for connect-job.com domain family
    if (uri.host.endsWith('connect-job.com')) {
      return uri.replace(scheme: 'https', host: _canonicalHost);
    }
    return uri;
  }

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.white)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) => setState(() => _isLoading = true),
          onNavigationRequest: (request) {
            // Normalize host/protocol to keep cookies valid (avoid jumping between www/non-www or http/https)
            try {
              final uri = Uri.parse(request.url);
              final canon = _toCanonical(uri);
              if (canon.toString() != request.url) {
                _controller.loadRequest(canon);
                return NavigationDecision.prevent;
              }
            } catch (_) {}
            return NavigationDecision.navigate;
          },
          onPageFinished: (_) async {
            setState(() => _isLoading = false);
            // Patch links that open in a new window/tab so they stay in this WebView
            await _controller.runJavaScript('''
              (function(){
                try {
                  // Force window.open to use same window
                  window.open = function(url){ window.location.href = url; return null; };
                  // Ensure all anchors open in same frame and normalized to canonical host/protocol
                  var as = document.querySelectorAll('a');
                  for (var i = 0; i < as.length; i++) {
                    try {
                      as[i].setAttribute('target','_self');
                      var a = document.createElement('a'); a.href = as[i].href;
                      if (a.host.endsWith('connect-job.com')) {
                        if (!a.host.startsWith('www.')) { a.host = 'www.' + a.host; }
                        a.protocol = 'https:';
                        as[i].href = a.href;
                      }
                    } catch(e2){}
                  }
                } catch(e) {}
              })();
            ''');
            // Optionally run post-load JS (e.g., pre-select role on /register)
            if (widget.postLoadJs != null && widget.postLoadJs!.trim().isNotEmpty) {
              try { await _controller.runJavaScript(widget.postLoadJs!); } catch (_) {}
            }
          },
        ),
      )
      ..loadRequest(_toCanonical(_startUri));
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => _controller.reload(),
          ),
          IconButton(
            icon: const Icon(Icons.open_in_browser),
            onPressed: () async {
              // Keep simple: just reload; deep OS open can be added later if needed
              _controller.loadRequest(Uri.parse(widget.url));
            },
          ),
        ],
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_isLoading)
            const Center(child: CircularProgressIndicator()),
        ],
      ),
    );
  }
}

