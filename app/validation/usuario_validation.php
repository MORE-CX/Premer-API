<?php

namespace App\Validation;

use App\Lib\Response;

class UsuarioValidation
{
    public static function validate($data, $update = false)
    {
        $response = new Response();
        $key = 'nombre';
        if (empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        } else {
            $value = $data[$key];

            if (strlen($value) < 4) {
                $response->errors[$key][] = 'Debe contener como minimo 4 caracteres';
            }
        }

        $key = 'email';
        if (empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        } else {
            $value = $data[$key];

            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $response->errors[$key][] = 'El valor ingresado no es un correo valido';
            }
        }

        $key = 'password';
        if (!$update) {
            if (empty($data[$key])) {
                $response->errors[$key][] = 'Este campo es obligatorio';
            } else {
                $value = $data[$key];

                if (strlen($value) < 4) {
                    $response->errors[$key][] = 'Debe contener como minimo 4 caracteres';
                }
            }
        } else {
            if (!empty($data[$key])) {
                $value = $data[$key];

                if (strlen($value) < 4) {
                    $response->errors[$key][] = 'Debe contener como minimo 4 caracteres';
                }
            }
        }

        $response->setResponse(empty($response->errors));
        return $response;
    }
}