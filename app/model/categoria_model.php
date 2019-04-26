<?php
namespace App\Model;

use App\Lib\Response;

class CategoriaModel
{
    private $db;
    private $table = 'categoria';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }




    public function listarCategorias()
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

        $categorias = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["categorias" => $categorias], "001");


    }

    public function obtenerCategoria($idCategoria){

        $categoria=$this->db->from($this->table,$idCategoria)->fetch();
        return $this->response->SetResponse(true, " ", ["categoria" => $categoria], "001");

    }


}