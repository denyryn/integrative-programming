<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePeminjamanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "anggota_id" => "sometimes|exists:anggotas,id",
            "tanggal_pinjam" => "sometimes|date",
            "jumlah_pinjam" => "sometimes|integer|min:1",
            "status" => "sometimes|in:pending,selesai",
        ];
    }
}
