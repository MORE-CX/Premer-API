<?php
namespace App\Model;

use App\Lib\Response;

class SucursalModel
{
    private $db;
    private $table = 'sucursal';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }

    public function listarsucursales($l, $p, float $latitud, float $longitud, $nivel)
    {
        /**
         * 
         * Suponiendo que 1 Grado de Latitud o Longitud son 111Km
         * Utilizaremos esta constante que podemos variar...
         * Es decir:
         * 0.01 ~ Radio de 2.25 Kms
         * 0.05 ~ Radio de 5.5  Kms
         * 0.1 ~ Radio de 11 Kms
         * 0.2 ~ Radio de 22 Kms
         * 0.5 ~ Radio de 50 kms
         * 
         */

        switch ($nivel) {
            case 1:
                $constante = 0.01;
                break;
            case 2:
                $constante = 0.05;
                break;
            case 3:
                $constante = 0.1;
                break;
            case 4:
                $constante = 0.2;
                break;
            case 5:
                $constante = 0.5;
                break;
        }

        $p = $p * $l;

        $LatParams = [
            ':lat_min' => $latitud - $constante,
            ':lat_max' => $latitud + $constante
        ];

        $LgnParams = [
            ':lng_min' => $longitud - $constante,
            ':lng_max' => $longitud + $constante
        ];

        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->select('ubicacion.latitud as latitud, ubicacion.longitud as longitud')
            ->innerJoin('ubicacion ON sucursal.ubicacion_id=ubicacion.id')
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->offset($p)
            ->fetchAll();
		

        $sucursalConCoordenada = [];
        foreach ($data as $value) {
			
			$total =
            $this->db
            ->from('unproducto')
            ->select('COUNT(*) Total')
			->where("sucursal_id", $value->id)
            ->fetch()
            ->Total;
			
            $sucursalConCoordenada[] = [
                'sucursal' => $value,
                'distancia' => $this->harvestine($latitud, $longitud, (float)$value->latitud, (float)$value->longitud),
				'cantidaddeproductos'=>$total
            ];
        }

        $sucursales = [
            'data' => $sucursalConCoordenada
        ];
        return $this->response->SetResponse(true, " ", ["sucursales" => $sucursales], "001");

    }



    public function obtenerSucursal($distancia,$sucursalId)
    {

        $data =
            $this->db->from($this->table)
            ->select('ubicacion.latitud as latitud, ubicacion.longitud as longitud')
            ->innerJoin('ubicacion ON sucursal.ubicacion_id=ubicacion.id')
			->where('sucursal.id',$sucursalId)
            ->fetch();
		

			
			$total =
            $this->db
            ->from('unproducto')
            ->select('COUNT(*) Total')
			->where("sucursal_id", $data->id)
            ->fetch()
            ->Total;
			
            $sucursalConCoordenada= [
                'sucursal' => $data,
                'distancia' => $distancia,
				'cantidaddeproductos'=>$total
            ];

        $sucursales = [
            'data' => $sucursalConCoordenada
        ];
        return $this->response->SetResponse(true, " ", ["sucursal" => $sucursales], "001");

    }


    public function harvestine($lat1, $long1, $lat2, $long2)
    {
        $km = 111.302;
        $degtorad = 0.01745329;
        $radtodeg = 57.29577951;
        $dlong = ($long1 - $long2);
        $dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad)) + (cos($lat1 * $degtorad) * cos($lat2 * $degtorad) * cos($dlong * $degtorad));
        $dd = acos($dvalue) * $radtodeg;

        return round(($dd * $km), 2);
    }




}