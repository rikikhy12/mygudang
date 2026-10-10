<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = Contact::latest();

        if ($type === 'customer') {
            $query->customers();
        } elseif ($type === 'supplier') {
            $query->suppliers();
        }

        $contacts = $query->get();

        return view('contacts.index', compact('contacts', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'customer');
        $isPopup = $request->boolean('popup');

        return view('contacts.create', compact('type', 'isPopup'));
    }

    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:customer,supplier,both'],
            'min_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'code' => ['required', 'string', 'unique:contacts,code'],
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string'],
        ]);

        $contact = Contact::create($request->all());

        if ($request->boolean('popup')) {
            return view('partials.window_opener_callback', [
                'title' => 'Kontak Rekanan Berhasil Disimpan',
                'payload' => [
                    'type' => 'contact_created',
                    'contact' => [
                        'id' => $contact->id,
                        'code' => $contact->code,
                        'name' => $contact->name,
                        'phone' => $contact->phone,
                        'address' => $contact->address,
                        'type' => $contact->type,
                        'label' => "{$contact->code} - {$contact->name}",
                    ],
                ],
                'redirectUrl' => route('contacts.index', ['type' => $request->type]),
            ]);
        }

        return redirect()->route('contacts.index', ['type' => $request->type])->with('success', 'Kontak berhasil disimpan.');
    }

    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'type' => ['required', 'in:customer,supplier,both'],
            'min_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'code' => ['required', 'string', Rule::unique('contacts')->ignore($contact->id)],
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $contact->update([
            'type' => $request->type,
            'min_margin_percent' => $request->min_margin_percent !== null && $request->min_margin_percent !== '' ? $request->min_margin_percent : null,
            'code' => $request->code,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'tax_number' => $request->tax_number,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('contacts.index', ['type' => $contact->type])->with('success', 'Kontak berhasil diperbarui.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Kontak berhasil dihapus.');
    }

    /**
     * AJAX quick store for modal popup in transaction forms
     */
    public function quickStore(Request $request)
    {
        $user = auth()->user();
        if (! $user->hasRole('admin') && ! $user->hasPermission('create_master_contacts')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membuat data Kontak/Supplier/Customer.',
            ], 403);
        }

        $type = $request->input('type', 'supplier');
        if (! in_array($type, ['customer', 'supplier', 'both'])) {
            $type = 'supplier';
        }

        $code = $request->input('code');
        if (empty($code)) {
            $prefix = ($type === 'customer') ? 'CUS' : (($type === 'both') ? 'CNT' : 'SUP');
            $code = $prefix.'-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(2)));
        }

        $validated = $request->validate([
            'type' => ['required', 'in:customer,supplier,both'],
            'code' => ['required', 'string', 'unique:contacts,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $validated['code'] = $code;
        $validated['is_active'] = true;

        $contact = Contact::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data '.($contact->type === 'customer' ? 'Customer' : 'Supplier').' baru berhasil disimpan.',
            'contact' => [
                'id' => $contact->id,
                'code' => $contact->code,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'type' => $contact->type,
                'label' => "{$contact->code} - {$contact->name}",
            ],
        ]);
    }
}
