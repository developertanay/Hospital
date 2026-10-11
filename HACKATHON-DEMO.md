# Reviewing the database-backed workflow

The former seeded A–E scenario is retired. Prepare actual hospitals, types, accounts and inventory through CRUD screens.

Review in this order:

1. Admin: create/edit hospital records and assign operator accounts.
2. Hospital: create a bed record, update blood units, edit contact/coordinates and accepting status.
3. Patient: create/edit a request, compare compatible hospitals and request a bed.
4. Hospital: verify severity, accept, record arrival, then discharge.
5. Patient: confirm reservation/admission/discharge status.
6. Check direct URLs with the wrong role; protected pages return 403.

No mock stock, distances or patient records are generated. Missing coordinates remain unrecorded. SQL workflow tests use isolated fixtures or rollback; existing records are preserved.

See [README](README.md) for details and test commands.
