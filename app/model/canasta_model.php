<?php
namespace App\Model;

use App\Lib\Response;

class CanastaModel
{
    private $db;
    private $table = 'canasta';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }


    public function listarCanastas($l, $p)
    {
        $p = $p * $l;
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->orderBy("fecha")
            ->fetchAll();

        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;
        $productos = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["canastas" => $productos], "001");


    }


    public function listarMisCanastas($l, $p, $idUsuario)
    {
        $p = $p * $l;
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->where("usuario_id=$idUsuario")
            ->orderBy("fecha")
            ->fetchAll();
        $canastas = [];
        foreach ($data as $unacanasta) {

            $productos = $this->db->from('canasta_contiene_unproducto')
                ->where('canasta_id = ' . $unacanasta->id)
                ->fetchAll();
            $precioTotal = 0;
            $cantidadDeProductos=0;
            foreach ($productos as $unprod) {
                $precio_id = $this->db->from('unproducto', $unprod->unproducto_id)->fetch()->precio_id;
                $cantidadDeProductos=$cantidadDeProductos+$unprod->cantidad;
                $precioTotal = $precioTotal +($unprod->cantidad*$this->db->from('precio', $precio_id)->fetch()->precio);
                
            }
            $canastas[] = [
                'canasta' => $unacanasta,
                'cantidaddeproductos' => $cantidadDeProductos,
                'preciototal' => round($precioTotal,2),
            ];

        }

        $miscanastas = [
            'data' => $canastas
        ];
        return $this->response->SetResponse(true, " ", ["canastas" => $miscanastas], "001");


    }
	
	public function listarMisCanastasSiContieneProducto($l, $p, $idUsuario,$idProducto)
    {
        $p = $p * $l;
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->where("usuario_id=$idUsuario")
            ->orderBy("fecha")
            ->fetchAll();
        $canastas = [];
        foreach ($data as $unacanasta) {

            $productos = $this->db->from('canasta_contiene_unproducto')
                ->where('canasta_id = ' . $unacanasta->id)
                ->fetchAll();
            $precioTotal = 0;
            $cantidadDeProductos=0;
			$locontiene=false;
            foreach ($productos as $unprod) {
                if($unprod->unproducto_id==$idProducto){
                    $locontiene=true;
                }
                $precio_id = $this->db->from('unproducto', $unprod->unproducto_id)->fetch()->precio_id;
                $cantidadDeProductos=$cantidadDeProductos+$unprod->cantidad;
                $precioTotal = $precioTotal +($unprod->cantidad*$this->db->from('precio', $precio_id)->fetch()->precio);
                
            }
            $canastas[] = [
                'canasta' => $unacanasta,
                'cantidaddeproductos' => $cantidadDeProductos,
                'preciototal' => $precioTotal,
				'locontiene'=>$locontiene
            ];

        }

        $miscanastas = [
            'data' => $canastas
        ];
        return $this->response->SetResponse(true, " ", ["canastas" => $miscanastas], "001");


    }

    public function agregarproductoacanasta($infoProducto)
    {

        $values = [
            'canasta_id' => $infoProducto['canasta_id'],
            'unproducto_id' => $infoProducto['unproducto_id'],
            'cantidad' => $infoProducto['cantidad'],
            'fecha' => date('Y-m-d H:i:s')
        ];
        
        $existe=$this->db->from('canasta_contiene_unproducto')
            ->select('COUNT(*) Total')
            ->where('canasta_id = ' . $infoProducto['canasta_id'].' and unproducto_id = '.$infoProducto['unproducto_id'])
            ->fetch();
 $total = $this->db->from('canasta_contiene_unproducto')
            ->select('COUNT(*) Total')
            ->where('canasta_id = ' . $infoProducto['canasta_id'])
            ->fetch()
            ->Total;
if($existe->Total==0){
        $query = $this->db->insertInto('canasta_contiene_unproducto', $values)->execute();
        
       
}else{
    $query=['error'=>'Ya existe producto en esta canasta','existe'=>$existe];
}
        $productos = [
            'data' => $query,
            'cantidaddeproductos' => $total
        ];
        return $this->response->SetResponse(true, " ", ["canastas" => $productos], "001");


    }


    public function listarproductosdecanasta($idCanasta,$latitud,$longitud)
    {
        
		
		$constante = 0.1;
        $LatParams = [
            ':lat_min' => $latitud - $constante,
            ':lat_max' => $latitud + $constante
        ];

        $LgnParams = [
            ':lng_min' => $longitud - $constante,
            ':lng_max' => $longitud + $constante
        ];
		
		$productosDeCanasta = $this->db->from('canasta_contiene_unproducto')
            ->where('canasta_id = ' . $idCanasta)
            ->fetchAll();
			
			

        $totalCanasta = 0;
		
        $canastas = [];
        
        foreach ($productosDeCanasta as $unProductoEnCanasta) {
            $unproducto = $this->db
                ->from('unproducto', $unProductoEnCanasta->unproducto_id)
                ->fetch();

            $producto = $this->db
                ->from('producto', $unproducto->producto_id)
                ->fetch();

            $precio = $this->db
                ->from('precio', $unproducto->precio_id)
                ->fetch();

            $sucursal = $this->db
                ->from('sucursal', $unproducto->sucursal_id)
                ->fetch();
				
			$ubicacion=$this->db
                ->from('ubicacion', $sucursal->ubicacion_id)
                ->fetch();

				
				
				
				
            $totalCanasta = $totalCanasta + $precio->precio;
			$distancia=$this->harvestine($latitud, $longitud, $ubicacion->latitud, $ubicacion->longitud);
            $infoCompleta = [
                'ids' => $unproducto,
                'producto' => $producto,
                'precio' => $precio,
                'sucursal' => $sucursal,
				'ubicacion'=>$ubicacion,
                'cantidad'=>$unProductoEnCanasta->cantidad,
				'distancia'=>$distancia
            ];

            $canastas[] = $infoCompleta;

        }
        $miscanastas = [
            'data' => $canastas
        ];
        return $this->response->SetResponse(true, " ", ["productos" => $miscanastas], "001");


    }
	
	
	
	
	
        public function quitarProductoDeCanasta($infoProducto){
        $exito=$this->db->deleteFrom('canasta_contiene_unproducto')
            ->where('canasta_id = ' . $infoProducto['canasta_id'].' and unproducto_id = '.$infoProducto['unproducto_id'])->execute();
			
			$fin=[
			'exito'=>$exito
			];
        return $this->response->SetResponse(true, " ", ["eliminar" => $fin], "001");
    }
    
    
   
    public function actualizarProductoDeCanasta($infoActualizacion){
        $set = ['cantidad' =>$infoActualizacion['cantidad']];
        $exito = $this->db->update('canasta_contiene_unproducto')->set($set)
            ->where('canasta_id = ' . $infoActualizacion['canasta_id'].' and unproducto_id = '.$infoActualizacion['unproducto_id'])
            ->execute();
        $fin=[
			'exito'=>$exito
			];
        return $this->response->SetResponse(true, " ", ["actualizar" => $fin], "001");
    }
    
    public function eliminarcanasta($idCanasta)
    {
        $data =
            $this->db->deleteFrom($this->table, $idCanasta)->execute();

        $info = [
            'data' => $data
        ];
        return $this->response->SetResponse(true, " ", ["eliminar" => $info], "001");
    }





    public function crearcanasta($infoCanasta)
    {
        $values = [
            'usuario_id' => $infoCanasta['usuario_id'],
            'nombre' => $infoCanasta['nombre'],
            'fecha' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->insertInto('canasta', $values)->execute();
        $canasta = $this->db->from('canasta', $id)
            ->fetch();
        $total = $this->db->from('canasta_contiene_unproducto')
            ->select('COUNT(*) Total')
            ->where('canasta_id = ' . $id)
            ->fetch()
            ->Total;
        $productos = [
            'canasta' => $canasta,
            'cantidaddeproductos' => $total
        ];
        return $this->response->SetResponse(true, " ", ["canasta" => $productos], "001");


    }
	
	
	public function crearCanastaBasica($idUsuario,$latitud,$longitud)
    {
		$constante = 0.1;
        $LatParams = [
            ':lat_min' => $latitud - $constante,
            ':lat_max' => $latitud + $constante
        ];

        $LgnParams = [
            ':lng_min' => $longitud - $constante,
            ':lng_max' => $longitud + $constante
        ];
		
		
		//-------------------BUSCAR-PRODUCTOS-----------------------
		$info=$this->db->from('unproducto')
			->select(null)
			->select('
			
			unproducto.id 					AS ids__id,
			unproducto.sucursal_id 			AS ids__sucursal_id,
			unproducto.producto_id 			AS ids__producto_id,
			unproducto.precio_id 			AS ids__precio_id,

			sucursal.id						AS sucursal__id,
			sucursal.empresa_id				AS sucursal__empresa_id,
			sucursal.ubicacion_id			AS sucursal__ubicacion_id,
			sucursal.nombre					AS sucursal__nombre,
			sucursal.telefono				AS sucursal__telefono,
			sucursal.image					AS sucursal__image,
			
			producto.id 					AS producto__id,
			producto.categoria_id 			AS producto__categoria_id,
			producto.nombre 				AS producto__nombre,
			producto.barcode 				AS producto__barcode,
			producto.marca 					AS producto__marca,
			producto.image 					AS producto__image,
			producto.cantidad 				AS producto__cantidad,
			producto.detalles 				AS producto__detalles,
			
			precio.id 						AS precio__id,
			precio.precio 					AS precio__precio,
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion,
			
			min(precio.precio)				AS precio__precio,
			
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud
			
			')
			->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
			->innerJoin("producto 			ON unproducto.producto_id=producto.id")
			->innerJoin("precio 			ON unproducto.precio_id=precio.id")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
			
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
			->where('producto.tipoproducto_id IS NOT NULL')
			
			->groupBy('producto.tipoproducto_id')
			->fetchAll();
			
			
			
		//-----------------------CREAR-CANASTA------------------------------
		
		
		 $values = [
            'usuario_id' => $idUsuario,
            'nombre' => 'Canasta Basica',
            'fecha' => date('Y-m-d H:i:s')
        ];

        $idCanasta = $this->db->insertInto('canasta', $values)->execute();
			
			
		
		
		
		
		
		
			
			foreach($info as $valor){
				$valor=(array)$valor;
				foreach($valor as $key => $inf){
						$porciones = explode('__', $key);
						$unproducto[$porciones[0]][$porciones[1]]=$inf;
						
					//-------INSERTAR-PRODUCTO-EN-CANASTA----------//	
					if($porciones[0]=='ids'&&$porciones[1]=='id'){
						$values = [
							'canasta_id' => $idCanasta,
							'unproducto_id' => $inf,
							'cantidad' => 2,
							'fecha' => date('Y-m-d H:i:s')
					];
					$this->db->insertInto('canasta_contiene_unproducto', $values)->execute();	
					}
				}
			}
		
		
		
		
			
        $productos = [
            'exito' => $idCanasta
        ];
		
		
		
		
		
        return $this->response->SetResponse(true, " ", ["canasta" => $productos], "001");


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