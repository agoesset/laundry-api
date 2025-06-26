<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

/**
 * Form Request untuk Register Authentication
 * 
 * Menangani validasi input register customer dengan custom error messages
 */
class AuthRegisterRequest extends FormRequest
{
    /**
     * Determine if user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'device_name' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi',
            'name.max' => 'Nama lengkap maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi',
            'password_confirmation.min' => 'Konfirmasi password minimal 8 karakter',
            'no_telp.required' => 'Nomor telepon wajib diisi',
            'no_telp.max' => 'Nomor telepon maksimal 20 karakter',
            'alamat.required' => 'Alamat wajib diisi',
            'device_name.required' => 'Nama device wajib diisi',
            'device_name.max' => 'Nama device maksimal 255 karakter',
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
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'no_telp' => 'Nomor Telepon',
            'alamat' => 'Alamat',
            'device_name' => 'Nama Device',
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
                'message' => 'Data registrasi tidak valid',
                'errors' => $errors,
            ], 422)
        );
    }
}