# UI refresh — CareRoute 1.5.3

This release changes web and Android presentation. Backend controllers, services, models, routes, migrations, configuration, `.env`, mobile API/session/location code and the APK server build settings are unchanged. A before/after SHA-256 comparison confirmed all **256 protected source/configuration files** match.

## Interface changes

| Web portal | Android app |
| --- | --- |
| Compact login with the existing fields and actions | Shorter login and connection text |
| Consistent teal accents, spacing and touch controls | Muted teal and mint theme with white cards |
| Forms stack on phones | Forms and lists keep a readable maximum width |
| Wide tables scroll within their containers | Phone bottom navigation and wide-screen side rail |
| Accessible menu toggle buttons | Wrapping labels and expanded dropdowns |
| Shorter inventory, request and coverage instructions | Shorter guidance; two recent activities first, then View all |
| Dashboard summaries and history default to two records | Completed trips default to two; active trips stay visible |

Existing role-based pages, actions, validation, request payloads, login behavior and booking rules are retained. Internal ambulance preference and External hospital preference remain intact. Essential connection errors, live/demo location distinctions, ETA estimates and driver/hospital confirmation notices remain visible.

## Screen-size verification

| Android test setting | Size | Text scale |
| --- | --- | --- |
| Small phone | 320 × 568 | 1.0 |
| Phone | 390 × 844 | 1.0 |
| Phone landscape | 844 × 390 | 1.0 |
| Tablet | 768 × 1024 | 1.0 |
| Desktop width | 1440 × 900 | 1.0 |
| Small phone, enlarged text | 320 × 568 | 1.5 |
| Tablet, enlarged text | 768 × 1024 | 2.0 |

The layout checks cover login, navigation, Request Care, request lists/details, hospital beds, family/insurance and account screens. Hospital, driver and admin navigation is also exercised at these settings. A separate small-screen check verifies the server dialog remains usable with enlarged text.

The live web hospital dashboard and inventory were checked at 320, 390, 768, 844 and 1440px widths. No page-level horizontal overflow was observed; the inventory table scrolls inside its container. The phone menu opens correctly. Blade templates compile successfully. Further live form browsing was interrupted by the browser connection; no form was submitted.

## Behavior checks

- 34 Flutter tests pass, including the existing login, request payload, Internal ambulance, External assistance, SOS and insurance checks.
- Flutter analysis passes.
- All five API test accounts log in successfully through Apache.
- 50 shared-database workflow checks pass; their writes are rolled back.
- Protected backend and connection source files are unchanged.
- Read-only web history tests verify local two/all/less toggles, unchanged API arrays, no extra network calls and preserved driver care/hospital selections.
- 19 dashboard detail checks pass; writes are rolled back.

Physical phone installation, GPS and photo-picker testing remain device checks. The tested sizes cover common layouts rather than every possible device.

## Preview

These images are rendered from the Flutter widgets using test data and bundled fonts. The sample server and patient/contact information are illustrative.

| Phone login | Tablet navigation |
| --- | --- |
| <img src="images/app-login-phone.png" width="220" alt="CareRoute phone login"> | <img src="images/app-home-tablet.png" width="400" alt="CareRoute tablet navigation"> |

## Run the checks

From `mobile/careroute`:

```sh
flutter analyze
flutter test
```

Optional preview regeneration uses the project-local SDK fonts. In PowerShell, set `$env:UI_PREVIEW='1'`, then run `flutter test test/responsive_test.dart`. This writes only the two test-data preview images in `docs/images`.

No migration, seeder, password reset or backend configuration change is required for this release.
