<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarCargoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            //
            "nombre_cargo"=>[
                "required",
                "string",
                Rule::unique('Cargo','nombre_cargo')->ignore($this->route("id_cargo"),'id_cargo')
            ],
            "descripcion_cargo"=>"required|string",
            "salario_cargo"=>"required|numeric",
            "id_jornada_laboral_diaria"=>"required",
        ];
    }
}
