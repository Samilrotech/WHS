// Basic Flutter widget test for WHS5 Mobile App

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:whs5_mobile/main.dart';

void main() {
  testWidgets('WHS5 app smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(
      const ProviderScope(
        child: WHS5App(),
      ),
    );

    // Verify that login screen is displayed initially
    expect(find.text('WHS5'), findsOneWidget);
    expect(find.text('Workplace Health & Safety'), findsOneWidget);
    expect(find.text('Login'), findsOneWidget);
  });
}
