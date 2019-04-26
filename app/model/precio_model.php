<?php
namespace App\Model;

use App\Lib\Response;

class PrecioModel
{
    private $db;
    private $table = 'precio';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }


    public function actualizarPrecio($infoActualizacion)
    {

        $idUsuario = $infoActualizacion['usuario_id'];
        $precio = $infoActualizacion['precio'];
        $precio_id = $infoActualizacion['precio_id'];

        $u_a_p = [
            'usuario_id' => $idUsuario,
            'precio_id' => $precio_id,
            'fecha' => date('Y-m-d H:i:s')
        ];

        $precio_id = $this->db->insertInto('usuario_actualiza_precio', $u_a_p)->execute();


        $productos = [
            'actualizado' => ($precio_id == 0) ? false : true
        ];

        return $this->response->SetResponse(true, " ", ["precio" => $productos], "001");
    }



}