if (isset($shippingData['shipping_contacts']) && is_array($shippingData['shipping_contacts'])) {
    $existingContactIds = DB::table('shipping_contacts_multiples')
        ->where('shipping_id', $address->id)
        ->pluck('shipping_contacts_id')
        ->toArray();

    $receivedIds = [];

    foreach ($shippingData['shipping_contacts'] as $contact) {
        $exit = $contact['exit'] ?? null;

        // Shared Contact
        if ($exit === 'on') {
            if (!empty($contact['id'])) {
                $contactModel = ShippingContact::findOrFail($contact['id']);
                $contactModel->update([
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                ]);
            } else {
                $contactModel = ShippingContact::create([
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                    'customer_id' => null, // Shared contact has no customer
                    'shipping_id' => null, // Not tied directly to a single shipping address
                ]);
            }

            // Ensure pivot relationship exists
            DB::table('shipping_contacts_multiples')->updateOrInsert([
                'shipping_id' => $address->id,
                'shipping_contacts_id' => $contactModel->id,
            ]);

            $receivedIds[] = $contactModel->id;
        } else {
            // Specific contact
            if (!empty($contact['id'])) {
                $contactModel = ShippingContact::findOrFail($contact['id']);
                $contactModel->update([
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                ]);
            } else {
                $contactModel = ShippingContact::create([
                    'shipping_id' => $address->id,
                    'customer_id' => $customer->id,
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                ]);
            }

            // For specific contact, no need to use pivot table
            $receivedIds[] = $contactModel->id;
        }
    }

    // Clean up: remove pivot relationships that no longer exist
    $toDetach = array_diff($existingContactIds, $receivedIds);
    if (!empty($toDetach)) {
        DB::table('shipping_contacts_multiples')
            ->where('shipping_id', $address->id)
            ->whereIn('shipping_contacts_id', $toDetach)
            ->delete();
    }

    // Optionally remove specific contacts no longer present
    $specificContacts = ShippingContact::where('shipping_id', $address->id)->pluck('id')->toArray();
    $toDeleteSpecific = array_diff($specificContacts, $receivedIds);
    if (!empty($toDeleteSpecific)) {
        ShippingContact::whereIn('id', $toDeleteSpecific)->delete();
    }
}
