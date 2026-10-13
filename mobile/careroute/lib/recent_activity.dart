import 'package:flutter/material.dart';

/// A compact view of history; all records remain available without another API call.
class RecentActivityList extends StatefulWidget {
  final List<Widget> children;
  final String title;

  const RecentActivityList({
    super.key,
    required this.children,
    this.title = 'Recent activity',
  });

  @override
  State<RecentActivityList> createState() => _RecentActivityListState();
}

class _RecentActivityListState extends State<RecentActivityList> {
  bool showAll = false;

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.stretch,
    children: [
      Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        children: [
          Text(widget.title, style: Theme.of(context).textTheme.titleMedium),
          if (widget.children.length > 2)
            TextButton(
              onPressed: () => setState(() => showAll = !showAll),
              child: Text(showAll ? 'View less' : 'View all'),
            ),
        ],
      ),
      ...widget.children.take(showAll ? widget.children.length : 2),
    ],
  );
}
