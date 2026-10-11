# Account access and workflow

Accounts and hospital assignments now come from SQL. There are no configured demo logins or default passwords.

1. Admin manages Hospital Master, types, Accounts & Hospital Access, modules and role assignments.
2. Admin assigns each hospital operator to an actual active hospital.
3. Hospital operator adds beds and blood stock using separate CRUD pages and edits hospital settings.
4. Patient creates a request and chooses a compatible hospital.
5. Hospital operator verifies severity, approves, records arrival and discharges the patient.
6. Patient sees only their own reservations/history. Hospital operators see their assigned hospital's records and new requests awaiting triage.

Use separate browser profiles when reviewing different roles. See [README](README.md) for schema, setup and verification.
