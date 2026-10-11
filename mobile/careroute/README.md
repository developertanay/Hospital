# CareRoute Flutter application

Patient-first Android/iOS app connected to the existing CareRoute Laravel backend.

The SQL workflow merge replaces the former fixed demo hospital/account mappings. Hospital IDs, care types and inventory are dynamic; mobile respects enabled accounts and assigned clinical modules. Admin uses the web management portal, without patient approval or reset controls. See the root README for additive migrations and account/hospital setup.

Version 1.1 includes patient SOS and nearest available demo ambulance driver dispatch. Read [SOS-DEMO.md](SOS-DEMO.md) for the complete patient → driver → hospital flow and driver logins.

Read [LOCAL-SETUP.md](LOCAL-SETUP.md) for local phone connectivity, Android/iOS build instructions, demo logins, complete patient/hospital flow, architecture and validation.

```sh
flutter pub get
flutter run
```

Set your API address using **Server connection** on the login screen. No public deployment is required when the phone and backend PC are on the same Wi-Fi.
