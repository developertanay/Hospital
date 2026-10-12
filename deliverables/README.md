# Hospital Android test build

Install `Hospital-Test.apk` on an Android 7.0+ device. App name: **CareRoute**, version **1.5.1 (9)**. This is a debug-signed local test build supporting ARM32, ARM64 and x86_64.

This build shows ambulance booking first for Internal requests and service-compatible hospital beds first for External requests. Both options remain available. Patients see nearby idle real-GPS ambulances and estimated arrival, book one selected vehicle, see its driver/contact details and track pickup. Selected bookings reserve the vehicle while the driver confirms. Hospital booking reaches only that hospital, and driver transport reuses the same care request. Follow [the demo guide](../docs/CARE-BOOKING-DEMO.md). File size: 162,038,535 bytes (approximately 155 MiB).

Use [the test account list](../docs/TEST-ACCOUNTS.md). All five test accounts use password `TestCare@2026`. The phone's server address is `http://10.9.4.210/Hospital/public/api/mobile`; adjust it in **Server connection** if the PC's Wi-Fi address changes. Keep XAMPP Apache/MySQL running and use the same Wi-Fi network.

Version 1.5.1 fixes closing the server connection dialog and prevents dismissal while the server is being checked. If the app remembers an old address, choose **Server connection → Current test server → Test & save**. The selected server is now visible on the login page.

For Internal cases, use **Continue to ambulance selection → Choose ambulance**. Missing patient GPS can be captured from the chooser. Only on-duty drivers sharing fresh live GPS can be booked; demo/offline drivers are excluded and the app explains the empty list. Availability loads automatically after the previous action finishes.

Version 1.5.0 adds **Account → Family & insurance**, dependent and policy/panel CRUD, private card-photo uploads and optional coverage on requests/SOS. **SOS for family / another person** separates the patient from the reporter and supports unknown details or a remote patient pickup pin. The booked hospital reviews coverage and cashless status separately, through the app or web desk. See [the complete flow](../docs/INSURANCE-FAMILY-SOS.md). The 44 new backend checks and 23 total Flutter tests pass; physical photo-picker/GPS testing is still needed.

Verified: APK v2 signature, package/version/Android compatibility, 23 Flutter UI tests, 13 Request Care detail checks, 30 care booking checks, 50 installed-database integration checks, and all five web/API logins through Apache. A physical phone was not connected for installation testing. The architecture and limitations are recorded in [WEB-APP-DATABASE.md](../docs/WEB-APP-DATABASE.md).

SHA-256: `B06EA3D51A035B35319DAA7CA4949536C54F5E8FD106D5683FFB8C062F02AF7E`.
