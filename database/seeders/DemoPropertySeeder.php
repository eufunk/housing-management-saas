<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Seeder;

/**
 * Fills the shared demo organization (see ProvisionDemoAccount) with
 * realistic example properties so the "Demo ausprobieren" flow has
 * something to look at beyond an empty list. Idempotent — safe to run
 * repeatedly, matches existing rows by name/email instead of duplicating.
 *
 * Run with: php artisan db:seed --class=DemoPropertySeeder
 */
class DemoPropertySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Hausverwaltung'],
        );

        $owners = collect([
            ['name' => 'Sabine Hoffmann', 'email' => 'sabine.hoffmann@example.test', 'phone' => '+49 30 1234501'],
            ['name' => 'Markus Weidner', 'email' => 'markus.weidner@example.test', 'phone' => '+49 40 1234502'],
            ['name' => 'Julia Sommerfeld', 'email' => 'julia.sommerfeld@example.test', 'phone' => '+49 89 1234503'],
            ['name' => 'Thomas Reinhardt', 'email' => 'thomas.reinhardt@example.test', 'phone' => '+49 221 1234504'],
            ['name' => 'Nordlicht Immobilien GbR', 'email' => 'kontakt@nordlicht-immobilien.test', 'phone' => '+49 40 1234505'],
        ])->map(fn (array $data) => Owner::firstOrCreate(
            ['organization_id' => $organization->id, 'email' => $data['email']],
            ['name' => $data['name'], 'phone' => $data['phone']],
        ));

        $properties = [
            ['name' => 'Wohnanlage Sonnenhof', 'street' => 'Lindenallee 14', 'postal_code' => '10115', 'city' => 'Berlin', 'owner' => 0],
            ['name' => 'Mehrfamilienhaus Kastanienweg', 'street' => 'Kastanienweg 7', 'postal_code' => '20095', 'city' => 'Hamburg', 'owner' => 1],
            ['name' => 'Stadtvilla am Rosenpark', 'street' => 'Rosenstraße 22', 'postal_code' => '80331', 'city' => 'München', 'owner' => null],
            ['name' => 'Wohnpark Rheinblick', 'street' => 'Rheinuferstraße 3', 'postal_code' => '50667', 'city' => 'Köln', 'owner' => 2],
            ['name' => 'Gartenhof Nord', 'street' => 'Ahornstraße 18', 'postal_code' => '60313', 'city' => 'Frankfurt am Main', 'owner' => null],
            ['name' => 'Mehrfamilienhaus Bergstraße 5', 'street' => 'Bergstraße 5', 'postal_code' => '70173', 'city' => 'Stuttgart', 'owner' => 3],
            ['name' => 'Wohnanlage Lindenpark', 'street' => 'Lindenstraße 9', 'postal_code' => '40210', 'city' => 'Düsseldorf', 'owner' => 4],
            ['name' => 'Altbauensemble Musterstraße', 'street' => 'Musterstraße 12', 'postal_code' => '04109', 'city' => 'Leipzig', 'owner' => 0],
            ['name' => 'Wohnhaus Am Stadtpark', 'street' => 'Am Stadtpark 4', 'postal_code' => '44135', 'city' => 'Dortmund', 'owner' => null],
            ['name' => 'Reihenhauszeile Fliederweg', 'street' => 'Fliederweg 11', 'postal_code' => '45127', 'city' => 'Essen', 'owner' => 1],
            ['name' => 'Mehrfamilienhaus Birkenallee', 'street' => 'Birkenallee 2', 'postal_code' => '28195', 'city' => 'Bremen', 'owner' => 4],
            ['name' => 'Wohnanlage Elbufer', 'street' => 'Elbuferstraße 6', 'postal_code' => '01067', 'city' => 'Dresden', 'owner' => 2],
            ['name' => 'Stadthaus Domblick', 'street' => 'Domplatz 8', 'postal_code' => '30159', 'city' => 'Hannover', 'owner' => null],
            ['name' => 'Wohnpark Nürnberger Tor', 'street' => 'Nürnberger Straße 15', 'postal_code' => '90402', 'city' => 'Nürnberg', 'owner' => 3],
            ['name' => 'Mehrfamilienhaus Kaiserstraße', 'street' => 'Kaiserstraße 33', 'postal_code' => '47051', 'city' => 'Duisburg', 'owner' => 0],
            ['name' => 'Wohnanlage Ruhrblick', 'street' => 'Ruhrstraße 21', 'postal_code' => '44787', 'city' => 'Bochum', 'owner' => null],
            ['name' => 'Gartenstadt Ost', 'street' => 'Ostendstraße 5', 'postal_code' => '06108', 'city' => 'Halle (Saale)', 'owner' => 1],
            ['name' => 'Mehrfamilienhaus Wallstraße', 'street' => 'Wallstraße 9', 'postal_code' => '33602', 'city' => 'Bielefeld', 'owner' => 4],
            ['name' => 'Wohnhaus Am Marktplatz', 'street' => 'Marktplatz 3', 'postal_code' => '24103', 'city' => 'Kiel', 'owner' => null],
            ['name' => 'Stadtvilla Klosterberg', 'street' => 'Klosterberg 6', 'postal_code' => '39104', 'city' => 'Magdeburg', 'owner' => 2],
        ];

        foreach ($properties as $data) {
            Property::firstOrCreate(
                ['organization_id' => $organization->id, 'name' => $data['name']],
                [
                    'owner_id' => $data['owner'] !== null ? $owners[$data['owner']]->id : null,
                    'street' => $data['street'],
                    'postal_code' => $data['postal_code'],
                    'city' => $data['city'],
                    'country' => 'DE',
                ],
            );
        }
    }
}
