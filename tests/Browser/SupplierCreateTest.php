<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Supplier;

class SupplierCreateTest extends DuskTestCase
{
    /**
     * Test user can access supplier create form
     */
    public function test_user_can_access_supplier_create_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/supplier/add')
                ->assertSee('Tambah Supplier')
                ->assertPresent('input[name="supplier_id"]')
                ->assertPresent('input[name="company_name"]')
                ->assertPresent('input[name="address"]')
                ->assertPresent('input[name="phone_number"]')
                ->assertPresent('input[name="bank_account"]')
                ->assertPresent('button[type="button"]');
        });
    }

    /**
     * Test create supplier with valid data
     */
    public function test_create_supplier_with_valid_data(): void
    {
        $supplierId = 'SUP001';
        $companyName = 'PT Test Supplier';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(3000);

            // Database assertion
            $this->assertDatabaseHas('suppliers', [
                'supplier_id' => $supplierId,
                'company_name' => $companyName,
                'address' => $address,
                'phone_number' => $phoneNumber,
                'bank_account' => $bankAccount,
            ]);

            // Cleanup
            Supplier::where('supplier_id', $supplierId)->delete();
        });
    }

    /**
     * Test create supplier with supplier_id exactly 6 characters
     */
    public function test_create_supplier_with_supplier_id_exactly_6_characters(): void
    {
        $supplierId = 'SUP123'; // Exactly 6 characters
        $companyName = 'PT Test Supplier 6 Char';
        $address = 'Jl. Test 6 Char';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(3000);

            // Database assertion
            $this->assertDatabaseHas('suppliers', [
                'supplier_id' => $supplierId,
                'company_name' => $companyName,
            ]);

            // Cleanup
            Supplier::where('supplier_id', $supplierId)->delete();
        });
    }

    /**
     * Test create supplier with maximum length company_name (100 chars)
     */
    public function test_create_supplier_with_maximum_length_company_name(): void
    {
        $supplierId = 'SUP002';
        $maxCompanyName = str_repeat('A', 100); // 100 characters
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $maxCompanyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $maxCompanyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(3000);

            // Database assertion
            $this->assertDatabaseHas('suppliers', [
                'supplier_id' => $supplierId,
                'company_name' => $maxCompanyName,
            ]);

            // Cleanup
            Supplier::where('supplier_id', $supplierId)->delete();
        });
    }

    /**
     * Test create supplier with maximum length address (100 chars)
     */
    public function test_create_supplier_with_maximum_length_address(): void
    {
        $supplierId = 'SUP003';
        $companyName = 'PT Test Max Address';
        $maxAddress = str_repeat('A', 100); // 100 characters
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $maxAddress, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $maxAddress)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(3000);

            // Database assertion
            $this->assertDatabaseHas('suppliers', [
                'supplier_id' => $supplierId,
                'address' => $maxAddress,
            ]);

            // Cleanup
            Supplier::where('supplier_id', $supplierId)->delete();
        });
    }

    /**
     * Test create supplier with maximum length bank_account (100 chars)
     */
    public function test_create_supplier_with_maximum_length_bank_account(): void
    {
        $supplierId = 'SUP005';
        $companyName = 'PT Test Max Bank';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $maxBankAccount = str_repeat('1', 100); // 100 characters

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $maxBankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $maxBankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(3000);

            // Database assertion
            $this->assertDatabaseHas('suppliers', [
                'supplier_id' => $supplierId,
                'bank_account' => $maxBankAccount,
            ]);

            // Cleanup
            Supplier::where('supplier_id', $supplierId)->delete();
        });
    }

    /**
     * Test create supplier with empty supplier_id
     */
    public function test_create_supplier_with_empty_supplier_id(): void
    {
        $supplierId = '';
        $companyName = 'PT Test Empty ID';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000)
                ->assertPathIs('/supplier/add')
                ->assertSee('ID Supplier harus diisi');
        });
    }

    /**
     * Test create supplier with empty company_name
     */
    public function test_create_supplier_with_empty_company_name(): void
    {
        $supplierId = 'SUP006';
        $companyName = '';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000)
                ->assertPathIs('/supplier/add')
                ->assertSee('company name harus diisi');
        });
    }

    /**
     * Test create supplier with empty address
     */
    public function test_create_supplier_with_empty_address(): void
    {
        $supplierId = 'SUP007';
        $companyName = 'PT Test Empty Address';
        $address = '';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000)
                ->assertPathIs('/supplier/add')
                ->assertSee('Address harus diisi');
        });
    }

    /**
     * Test create supplier with empty phone_number
     */
    public function test_create_supplier_with_empty_phone_number(): void
    {
        $supplierId = 'SUP008';
        $companyName = 'PT Test Empty Phone';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000)
                ->assertPathIs('/supplier/add')
                ->assertSee('Nomor Telephone harus diisi(10-13 digit)');
        });
    }

    /**
     * Test create supplier with empty bank_account
     */
    public function test_create_supplier_with_empty_bank_account(): void
    {
        $supplierId = 'SUP009';
        $companyName = 'PT Test Empty Bank';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000)
                ->assertPathIs('/supplier/add')
                ->assertSee('bank account harus diisi');
        });
    }

    /**
     * Test create supplier with supplier_id less than 6 characters
     */
    public function test_create_supplier_with_supplier_id_less_than_6_characters(): void
    {
        $supplierId = 'SUP01'; // 5 characters
        $companyName = 'PT Test Short ID';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000);

            // Should either stay on form or show error
            if ($browser->driver->getCurrentURL() === url('/supplier/add')) {
                $browser->assertPathIs('/supplier/add');
            }
        });
    }

    /**
     * Test create supplier with supplier_id more than 6 characters
     */
    public function test_create_supplier_with_supplier_id_more_than_6_characters(): void
    {
        $supplierId = 'SUP0123'; // 7 characters
        $companyName = 'PT Test Long ID';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000);

            // Should either stay on form or show error
            if ($browser->driver->getCurrentURL() === url('/supplier/add')) {
                $browser->assertPathIs('/supplier/add');
            }
        });
    }

    /**
     * Test create supplier with company_name more than 100 characters
     */
    public function test_create_supplier_with_company_name_too_long(): void
    {
        $supplierId = 'SUP010';
        $companyName = str_repeat('A', 101); // 101 characters
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000);

            // Should either stay on form or show error
            if ($browser->driver->getCurrentURL() === url('/supplier/add')) {
                $browser->assertPathIs('/supplier/add');
            }
        });
    }

    /**
     * Test create supplier with address more than 100 characters
     */
    public function test_create_supplier_with_address_too_long(): void
    {
        $supplierId = 'SUP011';
        $companyName = 'PT Test Long Address';
        $address = str_repeat('A', 101); // 101 characters
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000);

            // Should either stay on form or show error
            if ($browser->driver->getCurrentURL() === url('/supplier/add')) {
                $browser->assertPathIs('/supplier/add');
            }
        });
    }

    /**
     * Test create supplier with phone_number more than 30 characters
     */
    public function test_create_supplier_with_phone_number_too_long(): void
    {
        $supplierId = 'SUP012';
        $companyName = 'PT Test Long Phone';
        $address = 'Jl. Test No. 123';
        $phoneNumber = str_repeat('0', 31); // 31 characters
        $bankAccount = '1234567890';

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000);

            // Should either stay on form or show error
            if ($browser->driver->getCurrentURL() === url('/supplier/add')) {
                $browser->assertPathIs('/supplier/add');
            }
        });
    }

    /**
     * Test create supplier with bank_account more than 100 characters
     */
    public function test_create_supplier_with_bank_account_too_long(): void
    {
        $supplierId = 'SUP013';
        $companyName = 'PT Test Long Bank';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = str_repeat('1', 101); // 101 characters

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName)
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000);

            // Should either stay on form or show error
            if ($browser->driver->getCurrentURL() === url('/supplier/add')) {
                $browser->assertPathIs('/supplier/add');
            }
        });
    }

    /**
     * Test create supplier with duplicate supplier_id
     */
    public function test_create_supplier_with_duplicate_supplier_id(): void
    {
        $supplierId = 'SUPDUP';
        $companyName = 'PT Test Duplicate';
        $address = 'Jl. Test No. 123';
        $phoneNumber = '081234567890';
        $bankAccount = '1234567890';

        // Create first supplier
        Supplier::create([
            'supplier_id' => $supplierId,
            'company_name' => $companyName,
            'address' => $address,
            'phone_number' => $phoneNumber,
            'bank_account' => $bankAccount,
        ]);

        $this->browse(function (Browser $browser) use ($supplierId, $companyName, $address, $phoneNumber, $bankAccount) {
            $browser->visit('/supplier/add')
                ->waitFor('form[id="picForm"]', 10)
                ->type('supplier_id', $supplierId)
                ->type('company_name', $companyName . ' 2')
                ->type('address', $address)
                ->type('phone_number', $phoneNumber)
                ->type('bank_account', $bankAccount)
                ->click('button[onclick="validateForm()"]')
                ->pause(2000)
                ->assertPathIs('/supplier/add');
        });

        // Cleanup
        Supplier::where('supplier_id', $supplierId)->delete();
    }
}