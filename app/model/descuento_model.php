<?php
namespace App\Model;

use App\Lib\Response;

class DescuentoModel
{
    private $db;
    private $table = 'descuento';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }


    public function listarDescuentos($l, $p)
    {
		
		$p=$p*$l;
        $descuentos =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->fetchAll();
        $infoDescuento = [];
        foreach ($descuentos as $descuento) {
            $ids = $this->db
                ->from('unproducto', $descuento->unproducto_id)
                ->fetch();			

			$precio=	$this->db
                ->from('precio', $ids->precio_id)
                ->fetch();	
				
			$producto=	$this->db
                ->from('producto', $ids->producto_id)
                ->fetch();	
				
				
			
			$calificaciones = $this->db
                ->from('usuario_califica_producto')
				->where('unproducto_id',$descuento->unproducto_id)
                ->fetchAll();
			$calificacionFinal=0;
			foreach($calificaciones as $calificacion){
				$calificacionFinal=$calificacionFinal+$calificacion->calificacion;
			}
			$calificacionFinal=(count($calificaciones)>0)?$calificacionFinal / (count($calificaciones)):0;
			
			$sucursal=	$this->db
                ->from('sucursal', $ids->sucursal_id)
                ->fetch();

            $ubicacion=$this->db
                ->from('ubicacion',$sucursal->ubicacion_id)
                ->fetch();
				
            $infoDescuento[] = [
                'ids' => $ids,
                'descuento' => $descuento,
				'producto' => $producto,
				'precio' => $precio,
                'sucursal' => $sucursal,
                'ubicacion'=>$ubicacion,
				'calificacion'=>$calificacionFinal
            ];

        }
        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;
        $losDescuentos = [
            'data' => $infoDescuento,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["descuentos" => $losDescuentos], "001");


    }
	
	
	public function listarDescuentosDeSucursal($idSucursal,$l, $p)
    {
        $data =
            $this->db->from($this->table)
            ->limit($l)
			->where("sucursal_id",$idSucursal)
			->orderBy("fechaInicio")
            ->offset($l*$p)
            ->fetchAll();
        $infoProducto = [];
		
        foreach ($data as $unproducto) {


            $producto = $this->db
                ->from('producto', $unproducto->producto_id)
                ->fetch();			$producto->nombre=ucfirst($producto->nombre);

            $precio = $this->db
                ->from('precio', $unproducto->precio_id)
                ->fetch();

            $sucursal = $this->db
                ->from('sucursal', $unproducto->sucursal_id)
                ->fetch();
				
			$infoProducto[] = [
					'ids' => $unproducto,
					'producto' => $producto,
					'precio' => $precio,
					'sucursal' => $sucursal
				];
	
		}
        
        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;
        $productos = [
            'data' => $infoProducto,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }




    public function obtenerDescuento($id)
    {
        $descuento = $this->db
            ->from($this->table, $id)
            ->fetch();
        if ($descuento) {

            $producto = $this->db
                ->from('producto', $descuento->producto_id)
                ->fetch();

            $precio = $this->db
                ->from('precio', $descuento->precio_id)
                ->fetch();

            $sucursal = $this->db
                ->from('sucursal', $descuento->sucursal_id)
                ->fetch();

            $infoCompleta = [
                'ids' => $descuento,
                'producto' => $producto,
                'precio' => $precio,
                'sucursal' => $sucursal
            ];
            return $this->response->SetResponse(true, " ", ["descuento" => $infoCompleta]);
        } else {
            return $this->response->SetResponse(false, "No se encuentra el descuento con tal id");
        }
    }


}