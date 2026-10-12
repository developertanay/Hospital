# Hospital Android test build

Install `Hospital-Test.apk` on an Android 7.0+ device. App name: **CareRoute**, version **1.4.1 (7)**. This is a debug-signed local test build supporting ARM32, ARM64 and x86_64.

This build shows ambulance booking first for Internal requests and service-compatible hospital beds first for External requests. Both options remain available. Patients see nearby idle real-GPS ambulances and estimated arrival, book one selected vehicle, see its driver/contact details and track pickup. Selected bookings reserve the vehicle while the driver confirms. Hospital booking reaches only that hospital, and driver transport reuses the same care request. Follow [the demo guide](../docs/CARE-BOOKING-DEMO.md). File size: 161,776,886 bytes (approximately 154 MiB).

Use [the test account list](../docs/TEST-ACCOUNTS.md). All five test accounts use password `TestCare@2026`. The phone's server address is `http://10.9.4.134/Hospital/public/api/mobile`; adjust it in **Server connection** if the PC's Wi-Fi address changes. Keep XAMPP Apache/MySQL running and use the same Wi-Fi network.

For Internal cases, use **Continue to ambulance selection → Choose ambulance**. Missing patient GPS can be captured from the chooser. Only on-duty drivers sharing fresh live GPS can be booked; demo/offline drivers are excluded and the app explains the empty list. Availability loads automatically after the previous action finishes.

Verified: APK v2 signature, package/version/Android compatibility, 16 Flutter UI tests, 13 Request Care detail checks, 30 care booking checks, 50 installed-database integration checks, and all five web/API logins through Apache. A physical phone was not connected for installation testing. The architecture and limitations are recorded in [WEB-APP-DATABASE.md](../docs/WEB-APP-DATABASE.md).

SHA-256: `9D8F0A7CC3B74E79CDCA9D28F9A2DE36F7D5E278ADACC1B39EAF9ABACDDC9738`.
