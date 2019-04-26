<?php
namespace App\Model;

use App\Lib\Response;

class TipoProductoModel
{
    private $db;
    private $table = 'tipoproducto';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }




    public function listarTiposDeProductos()
    {
        $data =
            $this->db->from($this->table)
            ->orderBy("nombre")
            ->fetchAll();

        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;

        $tiposdeproducto = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["tiposdeproducto" => $tiposdeproducto], "001");


    }
    


}