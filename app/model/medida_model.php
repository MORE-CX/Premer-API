<?php
namespace App\Model;

use App\Lib\Response;

class MedidaModel
{
    private $db;
    private $table = 'unidad';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }




    public function listarMedidas()
    {
        $data =
            $this->db->from($this->table)
            ->orderBy("texto")
            ->fetchAll();

        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;

        $medidas = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["medidas" => $medidas], "001");


    }


}