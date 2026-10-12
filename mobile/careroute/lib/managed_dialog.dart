import 'package:flutter/material.dart';

// Dispose form controllers only after the dialog's closing animation unmounts it.
Future<T?> showManagedDialog<T>({
  required BuildContext context,
  required WidgetBuilder builder,
  bool barrierDismissible = true,
}) async {
  final route = DialogRoute<T>(
    context: context,
    builder: builder,
    barrierDismissible: barrierDismissible,
  );
  final result = await Navigator.of(context).push(route);
  await route.completed;
  return result;
}
