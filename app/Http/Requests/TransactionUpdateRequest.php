<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

/**
 * Form Request untuk Update Transaction
 * 
 * Menangani validasi input update transaction dengan status flow validation
 */
class TransactionUpdateRequest extends FormRequest
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
            'status_order' => 'sometimes|in:Process,Done,Delivery',
            'status_payment' => 'sometimes|in:Pending,Success,Failed',
            'kg' => 'sometimes|numeric|min:0.1|max:100',
            'discount' => 'sometimes|numeric|min:0|max:100',
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
            $transaction = $this->route('id') ? \App\Models\Transaction::find($this->route('id')) : null;
            
            if (!$transaction) {
                $validator->errors()->add('transaction', 'Transaksi tidak ditemukan');
                return;
            }

            // Status flow validation - tidak bisa mundur
            if ($this->status_order) {
                $statusOrder = ['Process', 'Done', 'Delivery'];
                $currentIndex = array_search($transaction->status_order, $statusOrder);
                $newIndex = array_search($this->status_order, $statusOrder);
                
                if ($newIndex < $currentIndex) {
                    $validator->errors()->add('status_order', 
                        'Status order tidak bisa diubah dari ' . $transaction->status_order . ' ke ' . $this->status_order
                    );
                }
            }

            // Tidak bisa ubah status payment jika sudah Success
            if ($this->status_payment && $transaction->status_payment === 'Success' && $this->status_payment !== 'Success') {
                $validator->errors()->add('status_payment', 
                    'Status payment yang sudah Success tidak bisa diubah'
                );
            }

            // Validasi diskon maksimal jika diubah
            if ($this->discount) {
                $settings = \App\Models\LaundrySetting::where('is_active', true)->first();
                if ($settings && $this->discount > $settings->max_discount_percent) {
                    $validator->errors()->add('discount', 
                        'Diskon maksimal ' . $settings->max_discount_percent . '%'
                    );
                }
            }

            // Validasi minimum order jika berat diubah
            if ($this->kg) {
                $totalHarga = $this->kg * $transaction->price->harga;
                $settings = \App\Models\LaundrySetting::first();
                
                if ($settings && $totalHarga < $settings->minimum_order) {
                    $validator->errors()->add('kg', 
                        'Minimum order Rp ' . number_format($settings->minimum_order, 0, ',', '.')
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
            'status_order.in' => 'Status order tidak valid',
            'status_payment.in' => 'Status payment tidak valid',
            'kg.numeric' => 'Berat cucian harus berupa angka',
            'kg.min' => 'Berat cucian minimal 0.1 kg',
            'kg.max' => 'Berat cucian maksimal 100 kg',
            'discount.numeric' => 'Diskon harus berupa angka',
            'discount.min' => 'Diskon minimal 0%',
            'discount.max' => 'Diskon maksimal 100%',
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
            'status_order' => 'Status Order',
            'status_payment' => 'Status Payment',
            'kg' => 'Berat Cucian',
            'discount' => 'Diskon',
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
                'message' => 'Data update transaksi tidak valid',
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
                'message' => 'Anda tidak memiliki akses untuk mengubah transaksi',
            ], 403)
        );
    }
}