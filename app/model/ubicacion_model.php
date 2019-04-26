<?php
namespace App\Model;

use App\Lib\Response;

class UbicacionModel
{
    private $db;
    private $table = 'ubicacion';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }

    public function listarubicaciones($l, $p)
    {
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->fetchAll();

        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;
        $ubicaciones = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["ubicaciones" => $ubicaciones], "001");

    }




    public function obtenerUbicacion($id)
    {
        $ubicacion = $this->db
            ->from($this->table, $id)
            ->fetch();
        if ($ubicacion) {
            return $this->response->SetResponse(true, " ", ["ubicacion" => $ubicacion]);
        } else {
            return $this->response->SetResponse(false, "No se encuentra el ubicacion con tal id");
        }
    }

    


}