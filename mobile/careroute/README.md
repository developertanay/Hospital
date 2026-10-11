# CareRoute Flutter application

Version 1.3 connects web and Android to the same SQL accounts, hospitals, beds and ambulance dispatch. Use the current setup in [WEB-APP-DATABASE.md](../../docs/WEB-APP-DATABASE.md); older demo-driver instructions below are superseded. Patient, driver and hospital test accounts are provisioned with `MobileTestAccountsSeeder`. Driver identities come from `ambulance_drivers` and their linked `ambulance_details` records, rather than fixed email mappings.

Patient-first Android/iOS app connected to the existing CareRoute Laravel backend.

The SQL workflow merge replaces the former fixed demo hospital/account mappings. Hospital IDs, care types and inventory are dynamic; mobile respects enabled accounts and assigned clinical modules. Admin uses the web management portal, without patient approval or reset controls. See the root README for additive migrations and account/hospital setup.

Version 1.2 includes emergency profile setup, multi-driver SOS broadcasts, first-accept dispatch, patient pickup progress, estimated arrival time and phone call actions. Read [SOS-DEMO.md](SOS-DEMO.md) for the complete patient → driver → hospital flow and driver logins.

Read [LOCAL-SETUP.md](LOCAL-SETUP.md) for local phone connectivity, Android/iOS build instructions, demo logins, complete patient/hospital flow, architecture and validation.

```sh
flutter pub get
flutter run
```

Set your API address using **Server connection** on the login screen. No public deployment is required when the phone and backend PC are on the same Wi-Fi.
