import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import 'session.dart';
import 'managed_dialog.dart';

class PatientPortfolio extends StatefulWidget {
  final Session session;
  const PatientPortfolio({super.key, required this.session});
  @override
  State<PatientPortfolio> createState() => _PatientPortfolioState();
}

class _PatientPortfolioState extends State<PatientPortfolio> {
  Map<String, dynamic> data = {};
  bool loading = false;
  String? error;
  Session get s => widget.session;
  List<Map<String, dynamic>> list(String key) =>
      (data[key] as List? ?? []).cast<Map<String, dynamic>>();
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => load());
  }

  Future<void> load() async {
    if (!mounted) return;
    setState(() => loading = true);
    try {
      final next = await s.api.call('portfolio');
      if (mounted) {
        setState(() {
          data = next;
          error = null;
        });
      }
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> archive(String kind, Map<String, dynamic> row) async {
    final yes = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Remove saved record?'),
        content: const Text('Previous bookings remain in history.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Back'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Remove'),
          ),
        ],
      ),
    );
    if (yes != true) return;
    try {
      await s.api.request('$kind/${row['id']}', {}, method: 'DELETE');
      await load();
      await s.refresh();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.toString())));
      }
    }
  }

  Future<void> edit(String kind, [Map<String, dynamic> row = const {}]) async {
    final family = kind == 'dependents';
    final keys = family
        ? ['name', 'relationship', 'age', 'mobile']
        : ['policy_number', 'member_id', 'valid_from', 'valid_until'];
    final labels = family
        ? ['Patient name', 'Relationship', 'Age (optional)', 'Phone (optional)']
        : [
            'Policy number',
            'Member ID',
            'Valid from (YYYY-MM-DD)',
            'Valid until (YYYY-MM-DD)',
          ];
    final controllers = {
      for (final key in keys)
        key: TextEditingController(text: (row[key] ?? '').toString()),
    };
    String? organisation = row['organisation_id']?.toString(),
        dependent = row['dependent_id']?.toString(),
        tpa = row['tpa_id']?.toString(),
        card;
    if (!list('organisations').any((o) => o['id'].toString() == organisation)) {
      organisation = null;
    }
    if (!list('organisations').any((o) => o['id'].toString() == tpa)) {
      tpa = null;
    }
    if (!list('dependents').any((d) => d['id'].toString() == dependent)) {
      dependent = null;
    }
    bool coversFamily = row['covers_family'] == true,
        busy = false,
        removeCard = false;
    String? message;
    final form = GlobalKey<FormState>();
    await showManagedDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, set) => AlertDialog(
          title: Text(
            '${row.isEmpty ? 'Add' : 'Edit'} ${family ? 'family member' : 'insurance / panel'}',
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
                    if (!family) ...[
                      DropdownButtonFormField<String>(
                        initialValue: organisation,
                        isExpanded: true,
                        decoration: const InputDecoration(
                          labelText: 'Insurer / employer / scheme',
                        ),
                        items: list('organisations')
                            .where((o) => o['kind'] != 'TPA')
                            .map(
                              (o) => DropdownMenuItem(
                                value: o['id'].toString(),
                                child: Text('${o['name']} (${o['kind']})'),
                              ),
                            )
                            .toList(),
                        onChanged: (v) => set(() => organisation = v),
                        validator: (v) =>
                            v == null ? 'Select an organisation' : null,
                      ),
                      DropdownButtonFormField<String>(
                        initialValue: tpa,
                        isExpanded: true,
                        decoration: const InputDecoration(
                          labelText: 'TPA (optional)',
                        ),
                        items: [
                          const DropdownMenuItem<String>(
                            value: null,
                            child: Text('None'),
                          ),
                          ...list('organisations')
                              .where((o) => o['kind'] == 'TPA')
                              .map(
                                (o) => DropdownMenuItem(
                                  value: o['id'].toString(),
                                  child: Text(o['name'].toString()),
                                ),
                              ),
                        ],
                        onChanged: (v) => set(() => tpa = v),
                      ),
                      DropdownButtonFormField<String>(
                        initialValue: dependent,
                        isExpanded: true,
                        decoration: const InputDecoration(
                          labelText: 'Covered person',
                        ),
                        items: [
                          const DropdownMenuItem<String>(
                            value: null,
                            child: Text('Myself'),
                          ),
                          ...list('dependents').map(
                            (d) => DropdownMenuItem(
                              value: d['id'].toString(),
                              child: Text(d['name'].toString()),
                            ),
                          ),
                        ],
                        onChanged: (v) => set(() {
                          dependent = v;
                          if (v != null) coversFamily = false;
                        }),
                      ),
                      if (dependent == null)
                        SwitchListTile(
                          contentPadding: EdgeInsets.zero,
                          title: const Text(
                            'Family policy / family membership',
                          ),
                          value: coversFamily,
                          onChanged: (v) => set(() => coversFamily = v),
                        ),
                    ],
                    for (var i = 0; i < keys.length; i++)
                      Padding(
                        padding: const EdgeInsets.only(top: 10),
                        child: TextFormField(
                          controller: controllers[keys[i]],
                          decoration: InputDecoration(labelText: labels[i]),
                          keyboardType: keys[i] == 'age'
                              ? TextInputType.number
                              : TextInputType.text,
                          maxLength: keys[i] == 'age' ? 3 : 100,
                          validator: (v) {
                            if (family && i < 2 && (v ?? '').trim().isEmpty) {
                              return 'Required';
                            }
                            if (keys[i] == 'age' &&
                                (v ?? '').isNotEmpty &&
                                ((int.tryParse(v!) ?? 0) < 1 ||
                                    (int.tryParse(v) ?? 0) > 120)) {
                              return 'Enter age 1–120';
                            }
                            return null;
                          },
                        ),
                      ),
                    if (!family) ...[
                      OutlinedButton.icon(
                        onPressed: busy
                            ? null
                            : () async {
                                try {
                                  final photo = await ImagePicker().pickImage(
                                    source: ImageSource.gallery,
                                    maxWidth: 1600,
                                    imageQuality: 80,
                                  );
                                  if (photo == null) return;
                                  final bytes = await photo.readAsBytes();
                                  if (bytes.length > 2097152) {
                                    throw Exception(
                                      'Choose a card photo under 2 MB.',
                                    );
                                  }
                                  if (ctx.mounted) {
                                    set(() {
                                      card = base64Encode(bytes);
                                      removeCard = false;
                                    });
                                  }
                                } catch (e) {
                                  if (ctx.mounted) {
                                    set(() => message = e.toString());
                                  }
                                }
                              },
                        icon: const Icon(Icons.add_photo_alternate_outlined),
                        label: Text(
                          card == null
                              ? 'Upload insurance card'
                              : 'Card photo selected',
                        ),
                      ),
                      if (row['has_card'] == true)
                        CheckboxListTile(
                          contentPadding: EdgeInsets.zero,
                          title: const Text('Remove existing card photo'),
                          value: removeCard,
                          onChanged: (v) => set(() => removeCard = v!),
                        ),
                      const Text(
                        'Details are self-reported. Hospital verification and cashless approval are separate.',
                      ),
                    ],
                    if (message != null)
                      Text(message!, style: const TextStyle(color: Colors.red)),
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
                      final body = <String, dynamic>{
                        for (final key in keys)
                          key: controllers[key]!.text.trim().isEmpty
                              ? null
                              : key == 'age'
                              ? int.parse(controllers[key]!.text.trim())
                              : controllers[key]!.text.trim(),
                      };
                      if (!family) {
                        body.addAll({
                          'organisation_id': organisation,
                          'tpa_id': tpa,
                          'dependent_id': dependent,
                          'covers_family': coversFamily,
                          'remove_card': removeCard,
                          'card_base64': ?card,
                        });
                      }
                      try {
                        await s.api.request(
                          row.isEmpty ? kind : '$kind/${row['id']}',
                          body,
                          method: row.isEmpty ? 'POST' : 'PUT',
                        );
                        if (ctx.mounted) Navigator.pop(ctx);
                      } catch (e) {
                        if (ctx.mounted) {
                          set(() {
                            busy = false;
                            message = e.toString();
                          });
                        }
                      }
                    },
              child: Text(busy ? 'Saving…' : 'Save'),
            ),
          ],
        ),
      ),
    );
    for (final c in controllers.values) {
      c.dispose();
    }
    await load();
    await s.refresh();
  }

  void viewCard(Map<String, dynamic> row) => showManagedDialog<void>(
    context: context,
    builder: (ctx) => AlertDialog(
      title: const Text('Insurance card'),
      content: Image.network(
        '${s.api.base}/coverages/${row['id']}/card',
        headers: {'Authorization': 'Bearer ${s.api.token}'},
        errorBuilder: (_, _, _) =>
            const Text('Card unavailable or access expired.'),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(ctx),
          child: const Text('Close'),
        ),
      ],
    ),
  );
  @override
  Widget build(BuildContext context) => RefreshIndicator(
    onRefresh: load,
    child: ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Text(
          'Family & insurance',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
        ),
        const Text(
          'Save family patients and insurance, employer or scheme memberships. You can still request care without coverage.',
        ),
        if (loading) const LinearProgressIndicator(),
        if (error != null)
          Text(error!, style: const TextStyle(color: Colors.red)),
        const SizedBox(height: 16),
        FilledButton.icon(
          onPressed: loading ? null : () => edit('dependents'),
          icon: const Icon(Icons.person_add_alt),
          label: const Text('Add family member'),
        ),
        for (final d in list('dependents'))
          Card(
            child: ListTile(
              title: Text(d['name'].toString()),
              subtitle: Text(
                '${d['relationship']} · ${d['age'] ?? 'Age unknown'}',
              ),
              onTap: () => edit('dependents', d),
              trailing: IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () => archive('dependents', d),
              ),
            ),
          ),
        const SizedBox(height: 16),
        FilledButton.icon(
          onPressed: loading ? null : () => edit('coverages'),
          icon: const Icon(Icons.shield_outlined),
          label: const Text('Add insurance / panel membership'),
        ),
        if (list('organisations').isEmpty && !loading)
          const Text(
            'An administrator needs to add insurance/panel organisations in the master first.',
          ),
        for (final c in list('coverages'))
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    (c['organisation'] as Map?)?['name']?.toString() ??
                        'Organisation unavailable',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  Text(
                    'Policy: ${c['policy_number'] ?? 'Not supplied'} · Member: ${c['member_id'] ?? 'Not supplied'}',
                  ),
                  Text(
                    'Valid until: ${c['valid_until'] ?? 'Not supplied'} · ${c['covers_family'] == true ? 'Family coverage' : 'Individual coverage'}',
                  ),
                  Wrap(
                    children: [
                      TextButton(
                        onPressed: () => edit('coverages', c),
                        child: const Text('Edit'),
                      ),
                      TextButton(
                        onPressed: () => archive('coverages', c),
                        child: const Text('Remove'),
                      ),
                      if (c['has_card'] == true)
                        TextButton(
                          onPressed: () => viewCard(c),
                          child: const Text('View card'),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ),
      ],
    ),
  );
}
