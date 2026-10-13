import 'package:flutter/material.dart';

/// Gives each page a readable width without changing its scrolling behavior.
class ResponsivePage extends StatelessWidget {
  final Widget child;
  final double maxWidth;
  final EdgeInsetsGeometry? padding;

  const ResponsivePage({
    super.key,
    required this.child,
    this.maxWidth = 1080,
    this.padding,
  });

  @override
  Widget build(BuildContext context) => LayoutBuilder(
    builder: (context, constraints) => Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: Padding(
          padding:
              padding ?? EdgeInsets.all(constraints.maxWidth < 360 ? 12 : 20),
          child: child,
        ),
      ),
    ),
  );
}

/// Keeps related sections side by side when there is room for both.
class ResponsivePair extends StatelessWidget {
  final Widget first;
  final Widget second;

  const ResponsivePair({super.key, required this.first, required this.second});

  @override
  Widget build(BuildContext context) => LayoutBuilder(
    builder: (context, constraints) => constraints.maxWidth >= 640
        ? Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: first),
              const SizedBox(width: 20),
              Expanded(child: second),
            ],
          )
        : Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [first, const SizedBox(height: 16), second],
          ),
  );
}
