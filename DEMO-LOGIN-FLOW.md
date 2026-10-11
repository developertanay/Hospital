# CareRoute — Login details and complete demo process

## Start here

- XAMPP: **Apache + MySQL running**. MySQL port `3306`, database `hospitals`.
- Login: http://localhost/Hospital/public/login
- Dashboard: http://localhost/Hospital/public/emergency
- All four NEW demo accounts use password **`CareRoute@2026`**.

| Login email | Role | Workspace |
| --- | --- | --- |
| `admin@careroute.test` | Admin | All working hospital modules, masters, inventory, assignments, users, all emergency requests and reset |
| `hospital.c@careroute.test` | Hospital Operator C | Triage new emergencies, accept/reject C requests, C beds and arrival |
| `hospital.b@careroute.test` | Hospital Operator B | Triage new emergencies, accept/reject B requests, B beds and arrival |
| `patient@careroute.test` | Patient / Requester | Create emergency, recommendations, request bed, own reservation status |

Use **Sign out** before changing accounts. Tabs in the same browser profile share a login; use separate browser profiles/private sessions if you want multiple roles simultaneously. A patient's requests persist across logout/login because they belong to the account, not the browser session. Patient cannot access admin Masters, cannot self-verify Critical, cannot accept reservations and cannot use the old PIN to gain hospital privileges.

## 1. Admin — prepare the demo

1. Sign in as `admin@careroute.test`.
2. Top menu has **Care coordination**, **Masters**, **Inventory**, **Administration**.
3. **Masters → Hospital Master** opens existing hospital records. **Bed Type Master** and **Blood Type Master** open their existing CRUD lists; Add opens creation forms.
4. **Inventory → Bed Inventory / Blood Inventory** opens legacy inventory lists.
5. **Administration → Modules & Role Assignments** shows the demo module matrix. **Legacy Module Assigning** opens the existing assignment screen; choose role and view/update assigned modules. Admin and Super Admin have every stored module assigned; Hospital Operator and Patient Requester have only relevant emergency modules.
6. **Administration → Demo Users & Login Guide** shows the four accounts and the workflow.
7. Return to **Dashboard**. Click **Reset demo data** and confirm to start with fresh emergency capacities. This resets only the JSON emergency mock store, not users, roles, module assignments, or MySQL hospital data.
8. Sign out.

**Important:** Existing SQL hospital/inventory records and emergency mock hospital A–E are separate. Editing a legacy inventory count does not change the emergency demo. This is intentional for the MVP; demonstrate the live reservation state using the emergency dashboard.

## 2. Patient — create and request the first ICU bed

1. Sign in as `patient@careroute.test`.
2. Choose **Emergency Request** or stay on Dashboard.
3. Click **Load accident scenario**: patient `Accident Patient 01`, location `Accident Site`, severity `Critical`, care `ICU`.
4. Click **Submit Emergency Request**.
5. Show recommendations: A (5 km) is rejected because zero ICU beds are available; E is offline. C (8 km, one ICU) is recommended, B (10 km, two ICU) is compatible.
6. Severity is still **Unverified / P1**. Declaring Critical does not increase priority.
7. At Hospital C click **Request ICU Bed**. Status becomes **Pending**, C's ICU gets an amber temporary hold, and the five-minute countdown starts.
8. Sign out. The request and temporary hold remain stored.

## 3. Hospital C — verify, then accept

1. Sign in as `hospital.c@careroute.test`.
2. Go to **Hospital Approval Queue**. Choose the first patient.
3. Select `Critical` in the verified-severity selector and click **Verify severity**. The request becomes verified **P3**.
4. Click **Accept → Reserved**. The ICU bed turns blue and the reservation countdown restarts at five minutes.
5. Show **Bed Dashboard**: this operator sees C's beds. Another hospital cannot accept or alter this request.
6. Sign out. Do NOT click arrival yet if you want to show Reserved in the patient view first.

## 4. Patient — track reservation and request the second patient

1. Sign back in as `patient@careroute.test`.
2. Open **Reservation Status & Decision Log** / queue. First patient shows **Reserved**, assigned Hospital C and bed ID.
3. Click **Load accident scenario** again; the next patient ID is filled. Submit Critical / ICU / Accident Site.
4. C now has zero free ICU because the first reservation is confirmed. **B becomes recommended**. A remains rejected.
5. Click B's **Request ICU Bed**, then sign out.

## 5. Hospital B — accept the second request

1. Sign in as `hospital.b@careroute.test`.
2. Open **Hospital Approval Queue**. Verify the second patient as Critical.
3. Click **Accept → Reserved**. B's ICU becomes Reserved.
4. Hospital B cannot accept C's request; cross-hospital actions return `403`.
5. Sign out. Patient can sign back in and see both Reserved statuses.

## 6. Hospital C — complete admission

1. Sign in as Hospital C again.
2. In the first patient's queue entry click **Patient arrived → Occupied**.
3. C's ICU becomes **Occupied**. It has no reservation expiry and will never be removed by another Critical request.
4. Admin can inspect the full decision log and both hospital dashboards. Patient sees their own statuses across login sessions.

## Optional: show emergency priority reclaim

1. Admin resets emergency data and signs out.
2. Patient creates `Stable Demo`, Stable / ICU, and requests C. **Do not accept this hold.**
3. Patient creates `Critical Demo`, Critical / ICU, but does not request a hospital yet.
4. Hospital C signs in; the new, unassigned Critical Demo is visible in the triage queue. Verify Critical, then sign out.
5. Patient signs in again. Critical Demo is now P3 and C's lower-priority hold is shown as reclaimable. Request C.
6. Stable Demo changes to **Released**; Critical Demo has C's pending hold. Select Stable Demo and request B to reassign it.
7. Hospital C accepts Critical Demo. Confirmed/occupied beds and equal-priority Critical holds are never reclaimed. Urgent queues ahead of Stable, but only verified Critical can reclaim temporary lower-priority holds.

## Expiry and abuse demo

- Leave Pending or Reserved untouched for five minutes. Next dashboard poll releases the bed and shows Expired. Occupied does not expire.
- Submit the same active patient ID twice: duplicate rejection.
- Submit more than five requests from one IP within a minute: rate-limit rejection; wait one minute.
- Patient calling verification or acceptance is denied server-side, not just hidden in the UI.
- Login uses Laravel password hashing and failed-login throttling. The old shared operator PIN cannot authorize anything.

## Recreate accounts on another machine

```powershell
php artisan db:seed --class=CareRouteDemoSeeder
php artisan view:clear
php artisan config:clear
```

Seeder adds modules/assignments and missing demo users, and never overwrites existing account passwords. Emergency JSON seeds on first access. For the legacy MySQL tables, use the existing `hospitals` database backup; the demo seeder does not recreate the full legacy schema.

## Checks

```powershell
php tests/emergency-smoke.php
pwsh -File tests/demo-role-flow.ps1
```

The role test resets the emergency mock seed before/after checking a mock patient. Sixteen admin module/form pages, patient ownership/status, role protection, hospital scoping, verification, acceptance and arrival are checked. The domain test passes 21 workflow checks.
