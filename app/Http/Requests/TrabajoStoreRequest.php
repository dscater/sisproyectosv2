<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrabajoStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proyecto_id' => 'required',
            'cliente_id' => 'required',
            'costo_original' => 'required|numeric|min:1',
            'moneda_seleccionada_id' => 'required',
            'estado_pago' => 'required',
            'descripcion' => 'required|min:4',
            'fecha_inicio' => 'required|date',
            'dias_plazo' => 'required|numeric',
            'fecha_entrega' => 'required|date',
            'estado_trabajo' => 'required',
            'fecha_envio' => 'nullable|date',
            'fecha_conclusion' => 'nullable|date',
        ];
    }

    /**
     * Mensajes de error
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            "proyecto_id.required" => "Este campo es obligatorio",
            "cliente_id.required" => "Este campo es obligatorio",
            "costo_original.required" => "Este campo es obligatorio",
            "costo_original.numeric" => "Debes ingresar un valor númerico",
            "costo_original.min" => "Debes ingresar al menos :min",
            "moneda_seleccionada_id.required" => "Este campo es obligatorio",
            "estado_pago.required" => "Este campo es obligatorio",
            "descripcion.required" => "Este campo es obligatorio",
            "descripcion.min" => "Debes ingresar al menos :min caracteres",
            "fecha_inicio.required" => "Este campo es obligatorio",
            "fecha_inicio.date" => "Debes ingresar una fecha valida",
            "dias_plazo.required" => "Este campo es obligatorio",
            "dias_plazo.numeric" => "Debes ingresar un valor númerico",
            "fecha_entrega.required" => "Este campo es obligatorio",
            "estado_trabajo.required" => "Este campo es obligatorio",
            "fecha_envio.required" => "Este campo es obligatorio",
            "fecha_envio.date" => "Debes ingresar una fecha valida",
            "fecha_conclusion.required" => "Este campo es obligatorio",
            "fecha_conclusion.date" => "Debes ingresar una fecha valida",
        ];
    }
}
