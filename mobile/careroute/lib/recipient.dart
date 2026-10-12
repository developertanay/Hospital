import 'package:flutter/material.dart';

import 'session.dart';
import 'managed_dialog.dart';
import 'location.dart';

Future<Map<String, dynamic>?> chooseSosRecipient(
  BuildContext context,
  Session s,
) async {
  String who = 'Self';
  final selection = await showDialog<String>(
    context: context,
    builder: (ctx) => SimpleDialog(
      title: const Text('Who needs help?'),
      children: [
        for (final entry in {
          'Self': 'For myself',
          'Family': 'For a saved family member',
          'Other': 'For another person / unknown patient',
        }.entries)
          SimpleDialogOption(
            onPressed: () => Navigator.pop(ctx, entry.key),
            child: Padding(
              padding: const EdgeInsets.all(10),
              child: Text(entry.value),
            ),
          ),
      ],
    ),
  );
  if (selection == null || !context.mounted) return null;
  who = selection;
  final portfolio = s.sosState ?? {};
  final dependents = (portfolio['dependents'] as List? ?? [])
      .cast<Map<String, dynamic>>();
  final coverages = (portfolio['coverages'] as List? ?? [])
      .cast<Map<String, dynamic>>();
  if (who == 'Family' && dependents.isEmpty) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Add a family member in Family & insurance first.'),
      ),
    );
    return null;
  }
  String? dependent, coverage;
  final profile = portfolio['patient_profile'] as Map? ?? {};
  final reporter = TextEditingController(
        text: (profile['name'] ?? s.profile?['name'] ?? '').toString(),
      ),
      phone = TextEditingController(text: (profile['mobile'] ?? '').toString());
  final name = TextEditingController(),
      age = TextEditingController(),
      patientPhone = TextEditingController(),
      description = TextEditingController(),
      address = TextEditingController(),
      lat = TextEditingController(),
      lng = TextEditingController();
  bool withPatient = true, busy = false;
  String? error;
  final form = GlobalKey<FormState>();
  Map<String, dynamic>? result;
  await showManagedDialog<void>(
    context: context,
    barrierDismissible: false,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, set) => AlertDialog(
        title: Text(
          who == 'Self'
              ? 'My SOS details'
              : who == 'Family'
              ? 'Family patient SOS'
              : 'SOS for another person',
        ),
        content: SizedBox(
          width: 440,
          child: SingleChildScrollView(
            child: Form(
              key: form,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (who == 'Family')
                    DropdownButtonFormField<String>(
                      initialValue: dependent,
                      isExpanded: true,
                      decoration: const InputDecoration(labelText: 'Patient'),
                      items: dependents
                          .map(
                            (d) => DropdownMenuItem(
                              value: d['id'].toString(),
                              child: Text(d['name'].toString()),
                            ),
                          )
                          .toList(),
                      onChanged: (v) => set(() {
                        dependent = v;
                        coverage = null;
                      }),
                      validator: (v) => v == null ? 'Choose the patient' : null,
                    ),
                  if (who == 'Other') ...[
                    TextFormField(
                      controller: name,
                      decoration: const InputDecoration(
                        labelText: 'Patient name (unknown allowed)',
                      ),
                      maxLength: 80,
                    ),
                    TextFormField(
                      controller: age,
                      decoration: const InputDecoration(
                        labelText: 'Approximate age (optional)',
                      ),
                      keyboardType: TextInputType.number,
                      validator: (v) => (v ?? '').isEmpty
                          ? null
                          : ((int.tryParse(v!) ?? 0) < 1 ||
                                (int.tryParse(v) ?? 0) > 120)
                          ? 'Enter age 1–120'
                          : null,
                    ),
                    TextFormField(
                      controller: patientPhone,
                      decoration: const InputDecoration(
                        labelText: 'Patient contact (optional)',
                      ),
                      keyboardType: TextInputType.phone,
                    ),
                  ],
                  if (who != 'Self') ...[
                    TextFormField(
                      controller: reporter,
                      decoration: const InputDecoration(
                        labelText: 'Your name — person raising SOS',
                      ),
                      maxLength: 80,
                      validator: (v) =>
                          (v ?? '').trim().isEmpty ? 'Required' : null,
                    ),
                    TextFormField(
                      controller: phone,
                      decoration: const InputDecoration(
                        labelText: 'Your contact number',
                      ),
                      keyboardType: TextInputType.phone,
                      validator: (v) =>
                          RegExp(r'^\+?[0-9]{8,15}$').hasMatch((v ?? '').trim())
                          ? null
                          : 'Use 8–15 digits; optional + prefix',
                    ),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('I am with the patient'),
                      value: withPatient,
                      onChanged: (v) => set(() => withPatient = v),
                    ),
                  ],
                  TextFormField(
                    controller: description,
                    decoration: const InputDecoration(
                      labelText: 'What happened / symptoms',
                    ),
                    maxLines: 3,
                    maxLength: 2000,
                    validator: (v) => who != 'Self' && (v ?? '').trim().isEmpty
                        ? 'Describe the problem'
                        : null,
                  ),
                  if (who != 'Other')
                    DropdownButtonFormField<String>(
                      key: ValueKey(dependent),
                      initialValue: coverage,
                      isExpanded: true,
                      decoration: const InputDecoration(
                        labelText: 'Insurance / panel (optional)',
                      ),
                      items: [
                        const DropdownMenuItem<String>(
                          value: null,
                          child: Text('No coverage / provide later'),
                        ),
                        ...coverages
                            .where(
                              (c) => who == 'Self'
                                  ? c['dependent_id'] == null
                                  : (c['dependent_id']?.toString() ==
                                            dependent ||
                                        (c['dependent_id'] == null &&
                                            c['covers_family'] == true)),
                            )
                            .map(
                              (c) => DropdownMenuItem(
                                value: c['id'].toString(),
                                child: Text(
                                  (c['organisation'] as Map?)?['name']
                                          ?.toString() ??
                                      'Coverage',
                                ),
                              ),
                            ),
                      ],
                      onChanged: (v) => set(() => coverage = v),
                    ),
                  if (!withPatient) ...[
                    const Text(
                      'Enter the patient’s pickup pin. Your phone GPS will not be used for their location.',
                    ),
                    TextFormField(
                      controller: address,
                      decoration: const InputDecoration(
                        labelText: 'Patient pickup address / landmark',
                      ),
                      validator: (v) =>
                          (v ?? '').trim().isEmpty ? 'Required' : null,
                      maxLength: 200,
                    ),
                    TextFormField(
                      controller: lat,
                      decoration: const InputDecoration(
                        labelText: 'Patient latitude',
                      ),
                      keyboardType: const TextInputType.numberWithOptions(
                        decimal: true,
                        signed: true,
                      ),
                      validator: (v) {
                        final n = double.tryParse(v ?? '');
                        return n == null || n.abs() > 90
                            ? 'Enter latitude -90 to 90'
                            : null;
                      },
                    ),
                    TextFormField(
                      controller: lng,
                      decoration: const InputDecoration(
                        labelText: 'Patient longitude',
                      ),
                      keyboardType: const TextInputType.numberWithOptions(
                        decimal: true,
                        signed: true,
                      ),
                      validator: (v) {
                        final n = double.tryParse(v ?? '');
                        return n == null || n.abs() > 180
                            ? 'Enter longitude -180 to 180'
                            : null;
                      },
                    ),
                  ] else
                    const Text(
                      'Pickup uses current GPS at the patient’s location.',
                    ),
                  const Text(
                    'Missing insurance or an unknown patient name does not block SOS.',
                  ),
                  if (error != null)
                    Text(error!, style: const TextStyle(color: Colors.red)),
                ],
              ),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: busy ? null : () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: busy
                ? null
                : () async {
                    if (!form.currentState!.validate()) return;
                    set(() => busy = true);
                    try {
                      final fix = withPatient
                          ? await PickupLocation.current()
                          : {
                              'lat': double.parse(lat.text),
                              'lng': double.parse(lng.text),
                              'accuracy': 0,
                              'captured_at':
                                  DateTime.now().millisecondsSinceEpoch ~/ 1000,
                              'mode': 'manual',
                            };
                      result = {
                        'reported_for': who,
                        'dependent_id': dependent,
                        'coverage_id': coverage,
                        'description': description.text.trim(),
                        if (who != 'Self') ...{
                          'reporter_name': reporter.text.trim(),
                          'reporter_phone': phone.text.trim(),
                          'reporter_with_patient': withPatient,
                        },
                        if (who == 'Other') ...{
                          'patient_name': name.text.trim().isEmpty
                              ? null
                              : name.text.trim(),
                          'patient_age': age.text.isEmpty
                              ? null
                              : int.parse(age.text),
                          'patient_phone': patientPhone.text.trim().isEmpty
                              ? null
                              : patientPhone.text.trim(),
                        },
                        if (!withPatient) 'pickup_address': address.text.trim(),
                        ...fix,
                      };
                      if (ctx.mounted) Navigator.pop(ctx);
                    } catch (e) {
                      if (ctx.mounted) {
                        set(() {
                          busy = false;
                          error = e.toString();
                        });
                      }
                    }
                  },
            child: Text(busy ? 'Getting location…' : 'Confirm SOS'),
          ),
        ],
      ),
    ),
  );
  for (final c in [
    reporter,
    phone,
    name,
    age,
    patientPhone,
    description,
    address,
    lat,
    lng,
  ]) {
    c.dispose();
  }
  return result;
}

Future<void> reviewInsurance(
  BuildContext context,
  Session s,
  Map<String, dynamic> r,
) async {
  String verification = r['insurance_status'] == 'NotSubmitted'
          ? 'Pending'
          : (r['insurance_status'] ?? 'Pending').toString(),
      cashless = (r['cashless_status'] ?? 'NotRequested').toString();
  final note = TextEditingController(
    text: r['insurance_note']?.toString() ?? '',
  );
  bool busy = false;
  String? error;
  await showManagedDialog<void>(
    context: context,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, set) => AlertDialog(
        title: const Text('Insurance / panel review'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'Record the actual verification and insurer/TPA decision. Bed acceptance does not approve cashless treatment.',
              ),
              DropdownButtonFormField<String>(
                initialValue: verification,
                decoration: const InputDecoration(
                  labelText: 'Coverage verification',
                ),
                items: ['Pending', 'Verified', 'Rejected']
                    .map((v) => DropdownMenuItem(value: v, child: Text(v)))
                    .toList(),
                onChanged: (v) => set(() => verification = v!),
              ),
              DropdownButtonFormField<String>(
                initialValue: cashless,
                decoration: const InputDecoration(
                  labelText: 'Cashless decision',
                ),
                items: ['NotRequested', 'Pending', 'Approved', 'Rejected']
                    .map((v) => DropdownMenuItem(value: v, child: Text(v)))
                    .toList(),
                onChanged: (v) => set(() => cashless = v!),
              ),
              TextField(
                controller: note,
                maxLength: 1000,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText:
                      'Verification note / approval reference (required)',
                ),
              ),
              if (error != null)
                Text(error!, style: const TextStyle(color: Colors.red)),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Back'),
          ),
          FilledButton(
            onPressed: busy
                ? null
                : () async {
                    if (note.text.trim().isEmpty) {
                      set(() => error = 'Enter a note or approval reference.');
                      return;
                    }
                    set(() => busy = true);
                    try {
                      await s.api.call('coverage/review', {
                        'request_id': r['id'],
                        'insurance_status': verification,
                        'cashless_status': cashless,
                        'insurance_note': note.text.trim(),
                      });
                      await s.refresh();
                      if (ctx.mounted) Navigator.pop(ctx);
                    } catch (e) {
                      if (ctx.mounted) {
                        set(() {
                          busy = false;
                          error = e.toString();
                        });
                      }
                    }
                  },
            child: const Text('Save decision'),
          ),
        ],
      ),
    ),
  );
  note.dispose();
}
