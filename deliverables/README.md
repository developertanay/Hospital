# Hospital Android test build

Install `Hospital-Test.apk` on an Android 7.0+ device. App name: **CareRoute**, version **1.3.0 (4)**. This is a debug-signed local test build supporting ARM32, ARM64 and x86_64.

Use [the test account list](../docs/TEST-ACCOUNTS.md). All five test accounts use password `TestCare@2026`. The phone's server address is `http://10.9.4.134/Hospital/public/api/mobile`; adjust it in **Server connection** if the PC's Wi-Fi address changes. Keep XAMPP Apache/MySQL running and use the same Wi-Fi network.

Verified: APK v2 signature, package/version/Android compatibility, 12 Flutter UI tests, 50 installed-database integration checks, and all five web/API logins through Apache. A physical phone was not connected for installation testing. The architecture and limitations are recorded in [WEB-APP-DATABASE.md](../docs/WEB-APP-DATABASE.md).

SHA-256: `73C77570005AC30F3037F3A7AB5B9177355F989E2FDA5EEC694966F92B2CCBF3`.
