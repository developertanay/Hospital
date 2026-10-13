# Request Care booking demo

Use Android **CareRoute 1.7.0 (13)** and the Laravel backend with migrations 000010 and 000011 applied. The APK is in `deliverables/Hospital-Test.apk`. Test accounts and password are listed in [TEST-ACCOUNTS.md](TEST-ACCOUNTS.md).

## Prepare the demo

1. Start XAMPP Apache/MySQL. Connect all test phones to the PC's Wi-Fi. Set **Server connection** to `http://10.9.6.195/Hospital/public/api/mobile` (update the IP if needed).
2. In the web hospital login, check the App Test Hospital coordinates, accepting status and available beds. Use coordinates near your demo location if demonstrating real nearest-hospital distances.
3. In admin masters, check active departments and hospital-to-department mappings. The labelled App Test Hospital has test mappings for General Medicine and Emergency Medicine. Dentistry should appear only for hospitals with an active Dentistry mapping and a suitable available bed.
4. Sign in as Driver 1 on another phone. Choose **Use my GPS** and go on duty with location permission. Keep the app open. Driver 2 can do the same on another device for an alternative. Test/SOS simulation mode does not make an ambulance eligible for the real-GPS Request Care list.
5. Save patient emergency contact details when prompted. Update placeholder driver/contact numbers and doctor details in Ambulance Master before demonstrating calls.

## Internal problem: ambulance first

1. Sign in as the patient, open **Request care**, select Internal, enter the description, service and care/bed type, and share GPS. Submit the form.
2. Tap **Continue to ambulance selection**, then the prominent **Choose ambulance** button at the top of request details. Vehicles are ordered by distance, with category, registered doctor information and estimated arrival. Hospital beds remain an alternative on request details. If pickup GPS was missing, use **Use current location to find ambulances** on the chooser.
3. Choose **Book this ambulance** and confirm. The app refreshes GPS and rechecks availability. The selected vehicle becomes unavailable to other patients while its driver confirms.
4. The patient sees driver/vehicle/contact details and ETA. Only the chosen driver gets the pickup request, patient details and description. Accept it from the driver app.
5. Open **Track pickup and trip** to see updates and use Maps/call actions. GPS and dispatch status update while the relevant apps are open. Arrival time is an estimate, not a road-routing guarantee.

## External injury: hospital first

1. Submit an External request with the required department (for example General Medicine or Emergency Medicine), care type, description and GPS.
2. The first option is a hospital bed. Only hospitals offering the selected service, accepting requests and having a compatible available bed appear. Distance ranks the options; a patient-selected compatible hospital preference may be prioritised.
3. Request a bed. That hospital alone sees the booking. Verify severity and accept from its hospital account; the patient sees Pending and then Reserved.
4. The ambulance section remains below as an optional pickup. Book an idle vehicle if desired. This uses the same care request, so driver transport does not create a duplicate hospital request.
5. Driver marks arrival at pickup, pickup and transport. Hospital marks patient arrival; driver completes handoff. The vehicle becomes idle again.

## Cases worth showing

- A pending selected-ambulance booking disappears from another patient's availability list.
- Offline, stale-GPS or busy vehicles are hidden. The list can legitimately be empty.
- If an on-duty test driver is still in demo mode, go off duty, disable demo coordinates and go online with live GPS. Keep that driver app open; availability explains when live GPS drivers are missing.
- A declined booking tells the patient to choose another vehicle; a cancellation or completed handoff releases it.
- A hospital without the required service cannot be booked, including through a crafted API call.
- An unbooked care form is not broadcast to unrelated hospitals.

Automated checks cover the shared database flow, selected-driver ownership, duplicate booking rejection, service filtering, linked bed booking and handoff. Physical phone GPS/installation and background push have not been tested or implemented, respectively; keep both apps open for this demonstration.
