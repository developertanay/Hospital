import 'package:flutter/material.dart';

import 'app.dart';
import 'session.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(CareRouteApp(session: Session()));
}
