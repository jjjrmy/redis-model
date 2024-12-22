<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Redis;

it('a customer can be created without id', function ($customerInput, $expect) {
    $customer = Customer::create($customerInput);
    expect($customer->name)->toEqual($expect['name']);
    expect($customer->email)->toEqual($expect['email']);
})->with([
    [
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
    ],
    [
        ['name' => 'Luke Downing', 'email' => 'luke_downing@example.com'],
        ['name' => 'Luke Downing', 'email' => 'luke_downing@example.com'],
    ],
    [
        ['name' => 'Freek Van Der Herten', 'email' => 'freek_van_der@example.com'],
        ['name' => 'Freek Van Der Herten', 'email' => 'freek_van_der@example.com'],
    ],
]);

it('can insert multiple customers without id', function ($data) {
    Customer::insert($data);

    $customers = Customer::get();
    expect($customers->count())->toBe(10);
})->with([
    function () {
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
            ];
        }

        return $data;
    }
]);

it('a customer can be force created', function ($customerInput, $expect) {
    $customer = Customer::create($customerInput);

    expect($customer->name)->toEqual($expect['name']);
    expect($customer->email)->toEqual($expect['email']);

    expect(Customer::count())->toBe(1);

    $customerForceCreate = Customer::forceCreate([
        'id' => $customer->id,
        'name' => $customer->name,
        'email' => $customer->email,
    ]);

    expect($customerForceCreate->id)->toEqual($customer['id']);
    expect($customerForceCreate->name)->toEqual($customer['name']);
    expect($customerForceCreate->email)->toEqual($customer['email']);

    expect(Customer::count())->toBe(1);
})->with([
    [
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
    ],
    [
        ['name' => 'Luke Downing', 'email' => 'luke_downing@example.com'],
        ['name' => 'Luke Downing', 'email' => 'luke_downing@example.com'],
    ],
    [
        ['name' => 'Freek Van Der Herten', 'email' => 'freek_van_der@example.com'],
        ['name' => 'Freek Van Der Herten', 'email' => 'freek_van_der@example.com'],
    ],
]);

it('a customer can be created, updated, and deleted', function ($customerInput, $expect) {
    $customer = Customer::create($customerInput);

    expect($customer->name)->toEqual($expect['name']);
    expect($customer->email)->toEqual($expect['email']);

    // Update the customer's name and email
    $customer->name = 'New Name';
    $customer->email = 'new_email@example.com';
    $customer->save();

    // Reload the customer from the database and assert that the name and email were updated
    $updatedCustomer = Customer::find($customer->id);
    expect($updatedCustomer->name)->toEqual('New Name');
    expect($updatedCustomer->email)->toEqual('new_email@example.com');

    // Delete the customer from the database
    $updatedCustomer->delete();

    // Assert that the customer was deleted by checking that it can no longer be found in the database
    $deletedCustomer = Customer::find($customer->id);
    expect($deletedCustomer)->toBeNull();
})->with([
    [
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
    ],
    [
        ['name' => 'Luke Downing', 'email' => 'luke_downing@example.com'],
        ['name' => 'Luke Downing', 'email' => 'luke_downing@example.com'],
    ],
    [
        ['name' => 'Freek Van Der Herten', 'email' => 'freek_van_der@example.com'],
        ['name' => 'Freek Van Der Herten', 'email' => 'freek_van_der@example.com'],
    ],
]);

it('can retrieve all customers', function () {
    expect(Customer::all()->count())->toBeGreaterThan(0);
})->with([
    [
        function () {
            $data = [];

            for ($i = 1; $i <= 10; $i++) {
                $data[] = [
                    'name' => 'Customer ' . $i,
                    'email' => 'customer' . $i . '@example.com',
                ];
            }

            return Customer::insert($data);
        }
    ],
    [
        fn() => Customer::create(['id' => 1, 'name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com']),
    ],
]);

it('can retrieve a single customer by ID', function (Customer $model, $expect) {
    $customer = Customer::find(1);

    expect($customer->name)->toEqual($expect['name']);
    expect($customer->email)->toEqual($expect['email']);
})->with([
    [
        fn() => Customer::create(['id' => 1, 'name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com']),
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
    ],
    [
        fn() => Customer::create(['id' => 1, 'name' => 'Nuno Madurop', 'email' => 'nuno_nadurop@example.com']),
        ['name' => 'Nuno Madurop', 'email' => 'nuno_nadurop@example.com'],
    ],
]);

it('can retrieve customers matching a given criteria', function (Customer $model, $expect) {
    $customers = Customer::where('name', 'Nuno*')->get();
    expect($customers->count())->toBeGreaterThan(0);

    foreach ($customers as $customer) {
        expect($customer->name)->toContain($expect['name']);
        expect($customer->email)->toContain($expect['email']);
    }
})->with([
    [
        fn() => Customer::create(['id' => 1, 'name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com']),
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
    ],
    [
        fn() => Customer::create(['id' => 1, 'name' => 'Nuno Madurop', 'email' => 'nuno_nadurop@example.com']),
        ['name' => 'Nuno Madurop', 'email' => 'nuno_nadurop@example.com'],
    ],
]);

it('can insert multiple customers', function ($data) {
    Customer::insert($data);

    $customers = Customer::get();
    expect($customers->count())->toBe(10);

    foreach ($data as $customerInput) {
        $customer = Customer::where('email', $customerInput['email'])->first();
        expect($customer->id)->toBeString();
        expect($customer->name)->toEqual($customerInput['name']);
        expect($customer->email)->toEqual($customerInput['email']);
    }
})->with([
    function () {
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'id' => $i,
            ];
        }

        return $data;
    }
]);

it('can remove multiple customers', function ($data) {
    Customer::insert($data);

    $customers = Customer::get();
    expect($customers->count())->toBe(10);

    Customer::where('email', 'customer' . rand(1, 3) . '@example.com')->destroy();
    Customer::where('email', 'customer' . rand(4, 6) . '@example.com')->destroy();
    Customer::where('email', 'customer' . rand(7, 10) . '@example.com')->destroy();

    expect(Customer::count())->toBe(7);

    Customer::destroy();

    expect(Customer::get()->count())->toBe(0);
})->with([
    function () {
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'id' => $i,
            ];
        }

        return $data;
    }
]);

it('it can insert multiple customers with transaction', function ($data) {
    Customer::transaction(function ($conTransaction) use ($data) {
        Customer::insert($data, $conTransaction);
    });

    expect(Customer::get()->count())->toBe(10);
})->with([
    function () {
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'id' => $i,
            ];
        }

        return $data;
    }
]);

it('it cant insert multiple customers with transaction', function ($data) {
    Customer::transaction(function ($conTransaction) use ($data) {
        Customer::insert($data, $conTransaction);

        throw new \Exception('Something went wrong');
    });

    expect(Customer::get()->count())->toBe(0);
})->with([
    function () {
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'id' => $i,
            ];
        }

        return $data;
    }
]);

it('can retrieve customers by email', function () {
    $customers = Customer::query()->where('email', 'nuno_naduro@example.com')->get();

    expect(2)->toEqual($customers->count());
})->with([
    [
        fn() => Customer::insert([
            ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
            ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
            ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.net'],
        ]),
    ],
    [
        fn() => Customer::insert([
            ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.net'],
            ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
            ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
        ]),
    ]
]);

it('can create customer assigning model property values', function (Customer $model, $expected) {
    $customer = Customer::query()->where('email', 'nuno_naduro@example.com')->first();

    expect($expected['name'])->toEqual($customer->name);
    expect($expected['email'])->toEqual($customer->email);
})->with([
    [
        function () {
            $customer = new Customer;
            $customer->name = 'Nuno Maduro';
            $customer->email = 'nuno_naduro@example.com';
            $customer->save();

            return $customer;
        },
        ['name' => 'Nuno Maduro', 'email' => 'nuno_naduro@example.com'],
    ]
]);

it('can update customer subKey without duplication', function () {
    expect(1)->toEqual(Customer::query()->count());
})->with([
    [
        function () {
            $customer = new Customer;
            $customer->name = 'Nuno Maduro';
            $customer->email = 'nuno_naduro@example.com';
            $customer->save();
            $customer->email = 'nuno_naduro@example.net';
            $customer->save();
        },
    ],
    [
        function () {
            $customer = new Customer;
            $customer->name = 'Nuno Maduro';
            $customer->email = 'nuno_naduro@example.com';
            $customer->save();
            $customer->name = 'Nuno';
            $customer->email = 'nuno_naduro@example.net';
            $customer->save();
        },
    ]
]);

it('can update customer primaryKey without duplication', function () {
    expect(1)->toEqual(Customer::query()->count());
})->with([
    [
        function () {
            $customer = new Customer;
            $customer->id = '1';
            $customer->name = 'Nuno Maduro';
            $customer->email = 'nuno_naduro@example.com';
            $customer->save();
            $customer->id = 2;
            $customer->save();
        },
    ],
    [
        function () {
            $customer = new Customer;
            $customer->id = 1;
            $customer->name = 'Nuno Maduro';
            $customer->email = 'nuno_naduro@example.com';
            $customer->save();
            $customer->id = 2;
            $customer->save();
        },
    ]
]);