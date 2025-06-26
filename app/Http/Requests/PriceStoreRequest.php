<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Price;

/**
 * Form Request untuk Create Price
 * 
 * Menangani validasi input create price dengan duplicate check
 */
class PriceStoreRequest extends FormRequest
{
    /**
     * Determine if user is authorized to make this request
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis' => 'required|string|max:255',
            'kg' => 'required|string|max:50',
            'harga' => 'required|numeric|min:1000|max:1000000',
            'hari' => 'required|integer|min:1|max:30',
            'status' => 'required|in:Active,Inactive',
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
            // Check duplicate jenis layanan yang aktif
            if ($this->jenis) {
                $exists = Price::where('jenis', $this->jenis)
                              ->where('status', 'Active')
                              ->exists();
                              
                if ($exists) {
                    $validator->errors()->add('jenis', 
                        'Jenis layanan "' . $this->jenis . '" sudah ada dan aktif'
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
            'jenis.required' => 'Jenis layanan wajib diisi',
            'jenis.max' => 'Jenis layanan maksimal 255 karakter',
            'kg.required' => 'Berat per kg wajib diisi',
            'kg.max' => 'Berat per kg maksimal 50 karakter',
            'harga.required' => 'Harga wajib diisi',
            'harga.numeric' => 'Harga harus berupa angka',
            'harga.min' => 'Harga minimal Rp 1.000',
            'harga.max' => 'Harga maksimal Rp 1.000.000',
            'hari.required' => 'Estimasi hari wajib diisi',
            'hari.integer' => 'Estimasi hari harus berupa angka',
            'hari.min' => 'Estimasi hari minimal 1 hari',
            'hari.max' => 'Estimasi hari maksimal 30 hari',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status harus Active atau Inactive',
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
            'jenis' => 'Jenis Layanan',
            'kg' => 'Berat per Kg',
            'harga' => 'Harga',
            'hari' => 'Estimasi Hari',
            'status' => 'Status',
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
                'message' => 'Data harga layanan tidak valid',
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
                'message' => 'Hanya admin yang bisa menambah harga layanan',
            ], 403)
        );
    }
}