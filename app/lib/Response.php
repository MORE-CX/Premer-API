<?php
namespace App\Lib;

class Response
{

    public $result = null;
    public $response = false;
    public $message = "Ocurrio un error inesperado.";
    public $errors = [];
    public $errorCode = "";
    public $data;

    public function SetResponse($response, $m = '', $data = null, $errorCode = "")
    {
        $this->response = $response;
        if ($m != false) {
            $this->message = $m;
        }
        if ($data != null) {
            $this->data = $data;
        }
        if (!$response && $m == '') {
            $this->message = 'Ocurrio un error inseperado.';
        }
        if ($errorCode != "") {
            $this->errorCode = $errorCode;
        }

        return $this;
    }


}
