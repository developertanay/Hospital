# Web and Android test logins

All accounts below use password **TestCare@2026** and are verified against the shared SQL database.

| Role | User ID |
|---|---|
| Patient | patient.test@careroute.test |
| Hospital | hospital.test@careroute.test |
| Driver 1 | driver1.test@careroute.test |
| Driver 2 | driver2.test@careroute.test |
| Admin | admin.test@careroute.test |

These are new, dedicated test accounts. Existing users and passwords were preserved. The hospital login is assigned to **App Test Hospital**, with three test beds. Driver 1 uses a hospital-associated test ambulance; Driver 2 uses a private test ambulance.

Web: `http://localhost/Hospital/public/login` on the PC. Android API server: `http://10.9.4.134/Hospital/public/api/mobile` in **Server connection**. Keep XAMPP Apache/MySQL running and connect phone and PC to the same Wi-Fi. If the PC's IP changes, update the app's server address.

To test SOS, keep driver and patient sessions open on separate devices or app/browser sessions. Drivers can explicitly select the app's labelled demo-location mode for an indoor test, or use real foreground GPS. Patients must first save emergency details. A driver accepts a pickup, records arrival and pickup, and requests transfer to the test hospital. The hospital verifies severity, accepts the request and confirms arrival before the driver completes handoff. Test contact numbers are placeholders; replace them with numbers you control before trying call buttons.
