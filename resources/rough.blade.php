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



{{-- SELECT 
    ROW_NUMBER() OVER (ORDER BY orders.created_at DESC) AS sr_no,
    customers.name AS customer,
    shipping_addresses.shipping_address AS shipping_address,
    drivers.name AS driver,
    orders.develivered_qty,
    orders.status,
    DATE_FORMAT(orders.created_at, '%d-%m-%Y') AS order_create_date,
    DATE_FORMAT(orders.updated_at, '%d-%m-%Y') AS order_completed_date
FROM orders
INNER JOIN customers ON customers.id = orders.customer_id
INNER JOIN drivers ON drivers.id = orders.driver_id
INNER JOIN shipping_addresses ON shipping_addresses.id = orders.shipping_id
WHERE orders.created_at BETWEEN '2025-09-01' AND CURRENT_DATE
ORDER BY orders.created_at DESC --}}
{{-- order page  --}}