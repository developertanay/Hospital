# CareRoute Flutter application

Patient-first Android/iOS app connected to the existing CareRoute Laravel backend.

Version 1.1 includes patient SOS and nearest available demo ambulance driver dispatch. Read [SOS-DEMO.md](SOS-DEMO.md) for the complete patient → driver → hospital flow and driver logins.

Read [LOCAL-SETUP.md](LOCAL-SETUP.md) for local phone connectivity, Android/iOS build instructions, demo logins, complete patient/hospital flow, architecture and validation.

```sh
flutter pub get
flutter run
```

Set your API address using **Server connection** on the login screen. No public deployment is required when the phone and backend PC are on the same Wi-Fi.
