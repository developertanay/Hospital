# CareRoute: 1–2 minute demo

## Before presenting

Open `http://localhost/Hospital/public/login`. Login as `admin@careroute.test` with `CareRoute@2026`, reset demo data, then use Patient, Hospital C and Hospital B logins as described in [DEMO-LOGIN-FLOW.md](DEMO-LOGIN-FLOW.md). Use separate browser profiles for a fast presentation. The interface labels all data as mock. Distances are fixed from Accident Site, not geocoded from the location field.

## Main demo (90 seconds)

**0–15 sec:** “Nearest hospital is not always the right hospital. CareRoute checks the required care before recommending a destination.” Click **Load accident scenario**, submit Critical / ICU / Accident Site.

**15–35 sec:** “Hospital A is 5 km away but has zero free ICU beds, so it is rejected. Hospital C is 8 km away with one ICU bed; B is 10 km away with two. C is recommended.” Show unverified standard priority. In Hospital desk queue, verify Critical. “A patient cannot award themselves emergency priority; a hospital operator verifies it.”

**35–55 sec:** Request ICU Bed at C. Show the temporary amber hold and five-minute countdown. Click **Accept → Reserved**. Show the blue reserved bed. “Hospital confirmation completes the reservation; a hold alone is not a confirmed admission.”

**55–80 sec:** Load and submit the second Critical accident patient, verify Critical. Show B now recommended. “C's confirmed reservation is protected. A second Critical patient goes to the next compatible hospital; nobody already occupied or confirmed is removed.” Request B and accept.

**80–100 sec:** Click **Patient arrived → Occupied** for first patient. “After arrival, the bed remains occupied; reservation expiry cannot release it. The decision log explains every allocation.”

## Optional priority-reclaim demonstration (30 seconds)

Reset. Submit **Stable Patient**, Stable / ICU, then request C **without accepting**. Submit **Critical Patient**, Critical / ICU; verify Critical. Request C: the lower-priority temporary hold is released. Select Stable Patient and request B. Show the release explanation in the queue and decision log. This rule never evicts an occupied patient or a hospital-confirmed reservation. Equal Critical holds are not displaced; verified queue priority is Critical, Urgent, then Stable/unverified, with FIFO ties.

## Five points for judges

1. **Compatibility before proximity:** incompatible care and offline hospitals are excluded before distance ranking.
2. **Verified urgency:** self-reported Critical does not grant Critical privileges.
3. **Protected allocation:** Critical can reclaim lower-priority unconfirmed holds, never occupied or confirmed beds.
4. **Explainable decisions:** rejection reasons, priority badges, expiry, and decision log make rules visible.
5. **Working coordination loop:** request, temporary hold, hospital acceptance, reservation, arrival, and expiry work end to end.

## Architecture in 20 seconds

One Laravel app serves a responsive Blade dashboard and JSON endpoints. MySQL stores demo users, roles, modules and assignments; Laravel authenticates accounts. A PHP service owns matching and bed transitions. Five hospitals and individual beds live in a seeded JSON file under `storage/app`; a file lock serializes updates to prevent double allocation. Browser fetch polling refreshes views every five seconds. Existing MySQL hospital modules remain separate. No new packages, live APIs, AI, payments, or GPS.

## Prototype boundaries

Laravel role logins now replace the operator PIN. Input location is descriptive; distances are seeded. Rate limit is five emergency submissions per IP per minute, duplicate active patient IDs are rejected, and failed account logins are throttled. These are basic abuse controls, not identity verification. Expiry is evaluated on each server read/action; no background worker is needed. UI state polling is not a live hospital integration. Reset only changes emergency demo JSON, never existing hospital/MySQL data.
