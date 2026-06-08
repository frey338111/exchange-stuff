<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PickupAddress;
use Illuminate\Database\Seeder;

class PickupAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses = [
            ['address_line_1' => '12 High Street', 'address_line_2' => null, 'city' => 'Bristol', 'county' => 'Bristol', 'postcode' => 'BS1 2AA'],
            ['address_line_1' => '45 King Street', 'address_line_2' => 'Flat 2', 'city' => 'Manchester', 'county' => 'Greater Manchester', 'postcode' => 'M2 4LQ'],
            ['address_line_1' => '8 Queen Road', 'address_line_2' => null, 'city' => 'Leeds', 'county' => 'West Yorkshire', 'postcode' => 'LS1 6AE'],
            ['address_line_1' => '21 Church Lane', 'address_line_2' => null, 'city' => 'Oxford', 'county' => 'Oxfordshire', 'postcode' => 'OX1 3AS'],
            ['address_line_1' => '3 Market Place', 'address_line_2' => 'Apartment 5', 'city' => 'Norwich', 'county' => 'Norfolk', 'postcode' => 'NR2 1ND'],
            ['address_line_1' => '76 Station Road', 'address_line_2' => null, 'city' => 'Cardiff', 'county' => 'Cardiff', 'postcode' => 'CF10 1EP'],
            ['address_line_1' => '14 Victoria Street', 'address_line_2' => null, 'city' => 'Edinburgh', 'county' => 'Midlothian', 'postcode' => 'EH1 2JL'],
            ['address_line_1' => '29 Park Avenue', 'address_line_2' => null, 'city' => 'Birmingham', 'county' => 'West Midlands', 'postcode' => 'B1 1BD'],
            ['address_line_1' => '5 Mill Road', 'address_line_2' => 'Unit 1', 'city' => 'Cambridge', 'county' => 'Cambridgeshire', 'postcode' => 'CB1 2AD'],
            ['address_line_1' => '91 London Road', 'address_line_2' => null, 'city' => 'Brighton', 'county' => 'East Sussex', 'postcode' => 'BN1 4JF'],
        ];

        Customer::query()
            ->orderBy('customer_id')
            ->get()
            ->each(function (Customer $customer, int $index) use ($addresses): void {
                $address = $addresses[$index % count($addresses)];

                PickupAddress::updateOrCreate(
                    ['customer_id' => $customer->customer_id],
                    [
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'address_line_1' => $address['address_line_1'],
                        'address_line_2' => $address['address_line_2'],
                        'city' => $address['city'],
                        'county' => $address['county'],
                        'postcode' => $address['postcode'],
                        'country' => 'GB',
                    ],
                );
            });
    }
}
