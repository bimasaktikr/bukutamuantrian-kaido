<?php


namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function createOrGet(array $data): Customer
    {
        return Customer::firstOrCreate(
            [
                'email' => $data['email'],
                'phone' => $this->normalizePhoneNumber($data['phone']),
            ],
            [
                'name' => $data['name'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'work_id' => $data['work_id'],
                'education_id' => $data['education_id'],
                'university_id' => $data['university_id'] ?? null,
                'institution_id' => $data['institution_id'] ?? null,
            ]
        );
    }

    public function normalizePhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $normalized = preg_replace('/\D/', '', $phoneNumber);

        // If the phone number starts with "0", replace it with "62"
        if (substr($normalized, 0, 1) === '0') {
            $normalized = '62' . substr($normalized, 1);
        }

        return $normalized;
    }


}
