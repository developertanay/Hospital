# Insurance, panels and SOS for another patient

Available in CareRoute Android 1.5.0 (8). Web and app use the existing MySQL database. Apply the additive migration `2026_10_12_000012_add_insurance_and_dependents.php`; existing accounts, passwords, beds and bookings remain intact.

## Masters and permissions

The web resources **Insurance & Panel Organisations** (`CoverageOrganisation`) and **Hospital Insurance & Panels** (`HospitalCoverage`) use resource controllers, models and the shared CRUD layout. Organisation kinds are Insurance, TPA, Employer and Scheme. Hospital mappings identify Cashless, Reimbursement or Panel arrangements.

Menus use `modules` and `module_assigning`. `CoverageSampleSeeder` provisions only missing assignments for enabled administrator roles; it preserves existing assignments and does not grant patient/driver access. Administrators can grant a hospital its mapping module through Module Assigning; hospital users then manage only their own hospital's mappings.

```powershell
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000012_add_insurance_and_dependents.php
C:\xampp\php\php.exe artisan db:seed --class=CoverageSampleSeeder
```

The seeder adds clearly labelled demo insurance, TPA, employer and scheme organisations and their App Test Hospital mappings. It does not create real memberships or imply an agreement with an actual insurer.

## Patient records

Open **Account → Family & insurance** in Android.

- Add, edit or archive a family member's name, relationship, optional age and phone.
- Add, edit or archive insurance/panel membership: organisation, optional TPA, policy number, member ID and validity dates.
- Select whether the record covers yourself, one dependent, or your family. A dependent cannot use another person's individual policy.
- Upload a JPG/PNG/WebP card photo up to 2 MB. Cards are private: only their owner and a hospital with a related booking can view them. Drivers and unrelated accounts cannot access cards or policy numbers.

The request-care form can select a saved dependent and suitable coverage. Hospital recommendations keep required service/bed compatibility and distance ranking, and add an empanelment/validity label. Coverage does not hide medically suitable non-panel hospitals or reserve a bed automatically.

## Verification and cashless decisions

The booked hospital sees policy/member details and the card in request details. In Android use **Review insurance / panel**; on the web approvals page use **Review insurance / panel** to open its form.

Coverage verification uses Pending, Verified and Rejected. Cashless status uses NotRequested, Pending, Approved and Rejected. Staff must enter a verification note or approval reference. Bed acceptance does not change these statuses. Recording cashless approval requires verified, currently valid coverage and an active cashless hospital arrangement.

Patient edits to coverage reset verification and cashless decisions on active related requests. Archived records preserve previous booking history. These are manual records of hospital/insurer decisions; there is no live insurer/TPA authorisation, claim submission, billing or payment integration.

## SOS recipient and pickup

The existing self-SOS button remains available, with optional self coverage. Use **SOS for family / another person** for the extended recipient flow:

1. Choose yourself, a saved family member, or another person/unknown patient.
2. For family, choose the dependent and an eligible individual/family coverage record. For an unrelated patient, your own policy cannot be attached.
3. For another patient, give your name/contact and a description. Patient name, approximate age and phone may be unknown.
4. If you are with the patient, use current GPS. If the patient is elsewhere, enter their address/landmark and pickup latitude/longitude. Their pin is stored as a manual patient location; the reporter's GPS is not substituted or streamed over it.
5. Confirm the details, then the app automatically captures the reporter's front-camera photo and records 10 seconds of audio. Both are mandatory for every SOS; interruption or denied permission sends no alert. The app refreshes live pickup GPS after capture and preserves remote manual pins. The reporter tracks the booking. The driver sees patient and reporting contact separately once assigned; unassigned broadcast offers conceal contact details. Hospital transport carries the patient identity, description, reporting contact and selected coverage into the linked care request.

Insurance is optional and does not block unknown-person SOS. [Automatic photo and voice capture](SOS-PHOTO.md) is required; eligible/assigned drivers see the reporter photo and can play the voice recording while policy details stay private. Live GPS updates require the app to stay open. Manual remote pins must come from the patient or someone at the pickup location.

## Verification

`tests/insurance-sos-smoke.php` checks ownership, dependent eligibility, private uploads, card access, master CRUD, hospital review, independent cashless decisions, policy-edit resets, family ambulance identity, driver policy redaction and unknown-person/remote-pin SOS. Database changes roll back; uploaded test files are removed. Existing shared-database smoke tests use temporary cloned patient/driver accounts to avoid interference with live demonstrations.

```powershell
C:\xampp\php\php.exe tests/insurance-sos-smoke.php
```

All 44 insurance/family/SOS checks and 19 Flutter tests passed. Android APK signing and version are checked separately. Physical-device photo selection and GPS still require device testing.
