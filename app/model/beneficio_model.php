<?php
namespace App\Model;

use App\Lib\Response;

class BeneficioModel
{
    private $db;
    private $table = 'beneficio';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }


    public function listarBeneficios($l, $p,$puntos)
    {
		
		$p=$p*$l;
        $beneficios =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->where("puntos_necesarios<=$puntos")
            ->fetchAll();
        $losBeneficios = [
            'data' => $beneficios
        ];
        return $this->response->SetResponse(true, " ", ["beneficios" => $losBeneficios], "001");


    }
	
	
}