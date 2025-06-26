<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Price;
use App\Models\LaundrySetting;

/**
 * Form Request untuk Create Transaction
 * 
 * Menangani validasi input create transaction dengan business rules
 */
class TransactionStoreRequest extends FormRequest
{
    /**
     * Determine if user is authorized to make this request
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isKaryawan());
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:users,id',
            'price_id' => 'required|exists:prices,id',
            'berat' => 'required|numeric|min:0.1|max:100',
            'catatan' => 'nullable|string|max:500',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'status_order' => 'nullable|in:Process,Done,Delivery',
            'status_payment' => 'nullable|in:Pending,Success,Failed',
        ];
    }

    /**
     * Configure the validator instance
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validasi customer harus role Customer
            if ($this->customer_id) {
                $customer = \App\Models\User::find($this->customer_id);
                if (!$customer || !$customer->isCustomer()) {
                    $validator->errors()->add('customer_id', 'User yang dipilih harus customer');
                }
            }

            // Validasi price harus aktif
            if ($this->price_id) {
                $price = Price::find($this->price_id);
                if (!$price || $price->status !== 'Active') {
                    $validator->errors()->add('price_id', 'Harga layanan tidak tersedia');
                }
            }

            // Validasi minimum order dari settings
            if ($this->berat && $this->price_id) {
                $price = Price::find($this->price_id);
                if ($price) {
                    $totalHarga = $this->berat * $price->harga;
                    $settings = LaundrySetting::first();
                    
                    if ($settings && $totalHarga < $settings->minimum_order) {
                        $validator->errors()->add('berat', 
                            'Minimum order Rp ' . number_format($settings->minimum_order, 0, ',', '.')
                        );
                    }
                }
            }

            // Validasi diskon maksimal
            if ($this->diskon) {
                $settings = LaundrySetting::where('is_active', true)->first();
                if ($settings && $this->diskon > $settings->max_discount_percent) {
                    $validator->errors()->add('diskon', 
                        'Diskon maksimal ' . $settings->max_discount_percent . '%'
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer wajib dipilih',
            'customer_id.exists' => 'Customer tidak ditemukan',
            'price_id.required' => 'Jenis layanan wajib dipilih',
            'price_id.exists' => 'Jenis layanan tidak ditemukan',
            'berat.required' => 'Berat cucian wajib diisi',
            'berat.numeric' => 'Berat cucian harus berupa angka',
            'berat.min' => 'Berat cucian minimal 0.1 kg',
            'berat.max' => 'Berat cucian maksimal 100 kg',
            'catatan.max' => 'Catatan maksimal 500 karakter',
            'diskon.numeric' => 'Diskon harus berupa angka',
            'diskon.min' => 'Diskon minimal 0%',
            'diskon.max' => 'Diskon maksimal 100%',
            'status_order.in' => 'Status order tidak valid',
            'status_payment.in' => 'Status payment tidak valid',
        ];
    }

    /**
     * Get custom attributes for validator errors
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'Customer',
            'price_id' => 'Jenis Layanan',
            'berat' => 'Berat Cucian',
            'catatan' => 'Catatan',
            'diskon' => 'Diskon',
            'status_order' => 'Status Order',
            'status_payment' => 'Status Payment',
        ];
    }

    /**
     * Handle a failed validation attempt
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Data transaksi tidak valid',
                'errors' => $errors,
            ], 422)
        );
    }

    /**
     * Handle a failed authorization attempt
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membuat transaksi',
            ], 403)
        );
    }
}