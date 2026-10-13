import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

import 'api.dart';

class SosSenderPhoto extends StatefulWidget {
  final CareApi api;
  final String url;
  const SosSenderPhoto({super.key, required this.api, required this.url});

  @override
  State<SosSenderPhoto> createState() => _SosSenderPhotoState();
}

class _SosSenderPhotoState extends State<SosSenderPhoto> {
  late Future<Uint8List> photo;
  String? token;

  @override
  void initState() {
    super.initState();
    token = widget.api.token;
    photo = load();
  }

  @override
  void didUpdateWidget(covariant SosSenderPhoto oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.url != widget.url ||
        oldWidget.api != widget.api ||
        token != widget.api.token) {
      token = widget.api.token;
      photo = load();
    }
  }

  Future<Uint8List> load() async {
    final server = Uri.parse(widget.api.base);
    final uri = server.resolve(widget.url);
    if (token == null ||
        uri.scheme != server.scheme ||
        uri.host != server.host ||
        uri.port != server.port ||
        uri.userInfo.isNotEmpty) {
      throw ApiFailure('Photo unavailable.');
    }
    final requestToken = token;
    final response = await widget.api.client
        .get(
          uri,
          headers: {
            'Accept': 'image/jpeg',
            'Authorization': 'Bearer $requestToken',
            'Cache-Control': 'no-store',
          },
        )
        .timeout(const Duration(seconds: 15));
    if (response.statusCode != 200 ||
        requestToken != widget.api.token ||
        response.bodyBytes.length > 2 * 1024 * 1024 ||
        response.bodyBytes.isEmpty) {
      throw ApiFailure('Photo unavailable.');
    }
    return response.bodyBytes;
  }

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 10),
    child: FutureBuilder<Uint8List>(
      future: photo,
      builder: (context, snapshot) {
        final bytes = snapshot.data;
        return Row(
          children: [
            SizedBox(
              width: 80,
              height: 80,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: bytes != null
                    ? InkWell(
                        onTap: () => showDialog<void>(
                          context: context,
                          builder: (ctx) => AlertDialog(
                            title: const Text('SOS sender photo'),
                            content: Image.memory(
                              bytes,
                              fit: BoxFit.contain,
                              errorBuilder: (_, _, _) =>
                                  const Icon(Icons.broken_image_outlined),
                            ),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(ctx),
                                child: const Text('Close'),
                              ),
                            ],
                          ),
                        ),
                        child: Image.memory(
                          bytes,
                          fit: BoxFit.cover,
                          errorBuilder: (_, _, _) =>
                              const Icon(Icons.broken_image_outlined),
                        ),
                      )
                    : ColoredBox(
                        color: const Color(0xFFF4F8F7),
                        child: snapshot.hasError
                            ? const Icon(Icons.broken_image_outlined)
                            : const Center(child: CircularProgressIndicator()),
                      ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                snapshot.hasError
                    ? 'SOS sender photo unavailable'
                    : 'SOS sender photo',
                style: const TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
            if (snapshot.hasError)
              IconButton(
                tooltip: 'Retry photo',
                onPressed: () => setState(() => photo = load()),
                icon: const Icon(Icons.refresh),
              ),
          ],
        );
      },
    ),
  );
}
