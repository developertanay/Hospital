# Account access and workflow

Accounts and hospital assignments now come from SQL. There are no configured demo logins or default passwords.

Existing account passwords are preserved. The separate SOS demo-driver identities are described in [SOS guide](mobile/careroute/SOS-DEMO.md); they do not grant hospital access. Patient/hospital/admin logins on the phone use SQL enabled status and role assignments. Former hospital.c/b/a/d/e accounts need an explicit real hospital assignment through **Accounts & Hospital Access**. The old A–E emergency JSON is preserved on disk but is no longer the active bed database.

1. Admin manages Hospital Master, types, Accounts & Hospital Access, modules and role assignments.
2. Admin assigns each hospital operator to an actual active hospital.
3. Hospital operator adds beds and blood stock using separate CRUD pages and edits hospital settings.
4. Patient creates a request and chooses a compatible hospital.
5. Hospital operator verifies severity, approves, records arrival and discharges the patient.
6. Patient sees only their own reservations/history. Hospital operators see their assigned hospital's records and new requests awaiting triage.

Use separate browser profiles when reviewing different roles. See [README](README.md) for schema, setup and verification.
