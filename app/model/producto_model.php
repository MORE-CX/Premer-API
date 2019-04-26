<?php
namespace App\Model;

use App\Lib\Response;

class ProductoModel
{
    private $db;
    private $table = 'unproducto';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }

    public function listarIDs($l, $p)
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
        $productos = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["productosIDs" => $productos], "001");

    }


    public function listarProductos($l, $p)
    {

        $p = $p * $l;
        $info = $this->db
            ->from('unproducto')
            ->select(null)
            ->limit($l)
            ->offset($p)
            ->select('
			
			unproducto.id 					AS ids__id,
			unproducto.sucursal_id 			AS ids__sucursal_id,
			unproducto.producto_id 			AS ids__producto_id,
			unproducto.precio_id 			AS ids__precio_id,
			unproducto.fecha_actualizacion 	AS ids__fecha_actualizacion,
			
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
			precio.esActual 				AS precio__esActual
			
			')
            ->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			ON unproducto.precio_id=precio.id")
            ->fetchAll();


        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $allProductos[] = $unproducto;
        }
        $data = [
            'data' => $allProductos
        ];

        return $this->response->SetResponse(true, " ", ["productos" => $data], "001");
    }


    public function votarPorPrecio($idPrecio, $idUsuario)
    {


        $valuesUserVotaPre = [
            'usuario_id' => $idUsuario,
            'usuario_postula_precio_id' => $idPrecio
        ];

        $idUsuarioVotaPrecio = $this->db->insertInto('usuario_vota_postulacion_de_precio', $valuesUserVotaPre)->execute();

        $data = [
            'exito' => $idUsuarioVotaPrecio
        ];

        return $this->response->SetResponse(true, " ", ["votarPorPrecio" => $data], "001");
    }



    public function usuariosQueQuierenActualizarPrecio($idPrecio, $idUsuario)
    {
        $data =
            $this->db->from('usuario_postula_precio')
            ->where('usuario_postula_precio.precio_id', $idPrecio)
            ->select(null)
            ->select('
			usuario_postula_precio.id 				AS postulacion__id,
			usuario_postula_precio.usuario_id 		AS postulacion__usuario_id,
			usuario_postula_precio.precio_id 		AS postulacion__precio_id,
			usuario_postula_precio.precio	 		AS postulacion__precio,
			usuario_postula_precio.fecha 			AS postulacion__fecha,
			
			usuario.image 							AS usuario__image,
			usuario.nombre 							AS usuario__nombre,
			usuario.puntos							AS usuario__puntos
			
			

			')
            ->innerJoin("usuario ON usuario_postula_precio.usuario_id = usuario.id")
            ->limit(10)
            ->orderBy('usuario_postula_precio.fecha')
            ->fetchAll();

        $actConVotos = [];

        foreach ($data as $unaActualizacion) {
            $calificacionFinal = 0;
            $valor = (array)$unaActualizacion;
            $unaActua = [];



            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unaActua[$porciones[0]][$porciones[1]] = $inf;

            }
            $votos = $this->db->from('usuario_vota_postulacion_de_precio')
                ->where('usuario_postula_precio_id', $unaActua['postulacion']['id'])
                ->select('COUNT(*) AS Total')
                ->fetch()
                ->Total;

            $vote = $this->db->from('usuario_vota_postulacion_de_precio')
                ->where('usuario_postula_precio_id', $unaActua['postulacion']['id'])
                ->where('usuario_id', $idUsuario)
                ->select('COUNT(*) AS Total')
                ->fetch()
                ->Total;

            $unaActua['votos'] = $votos;
            $unaActua['vote'] = $vote;
            $actConVotos[] = $unaActua;


        }
        $actualizaciones = [
            'data' => $actConVotos
        ];
        return $this->response->SetResponse(true, " ", ["postulaciones" => $actualizaciones], "001");

    }




    public function listarProductosPorLocalizacion($l, $p, $latitud, $longitud)
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

        $p = $p * $l;
        $info = $this->db
            ->from('unproducto')
            ->orderBy("precio__fecha_actualizacion DESC")
            ->select(null)
            ->limit($l)
            ->offset($p)
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
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion
			
			')
            ->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			ON unproducto.precio_id=precio.id")

            ->select(" 
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->where('unproducto.aprobado=1')
            ->fetchAll();

        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $distancia = $this->harvestine($latitud, $longitud, $unproducto['ubicacion']['latitud'], $unproducto['ubicacion']['longitud']);
            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $unproducto['distancia'] = $distancia;
            $allProductos[] = $unproducto;
        }


        $productos = [
            'data' => $allProductos
        ];


        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }


    public function busquedaDeProductos($l, $p, $filtros, $latitud, $longitud)
    {
        $maxPrecio = ($filtros["rangoPrecio"]["max"] == "") ? 2147483647 : $filtros["rangoPrecio"]["max"];
        $minPrecio = ($filtros["rangoPrecio"]["min"] == "") ? 0 : $filtros["rangoPrecio"]["min"];
        $nombre = $filtros["nombre"];

        $filtroCategoria = ($filtros["categoria"] == -999) ? "" : " AND categoria_id = " . $filtros["categoria"];

        $filtroPrecio = "(precio.precio <= $maxPrecio AND precio.precio >= $minPrecio)";
        $filtroNombre = " AND (producto.nombre LIKE '%$nombre%' OR producto.marca LIKE '%$nombre%' OR producto.barcode LIKE '%$nombre%')";
        $filtroSucursal = ($filtros["sucursal"] == -999) ? "" : " AND sucursal_id = " . $filtros["sucursal"];

        $constante = 0.1;
        $LatParams = [
            ':lat_min' => $latitud - $constante,
            ':lat_max' => $latitud + $constante
        ];

        $LgnParams = [
            ':lng_min' => $longitud - $constante,
            ':lng_max' => $longitud + $constante
        ];




        $p = $p * $l;

        $filtrosAll = $filtroPrecio . $filtroNombre . $filtroSucursal . $filtroCategoria;



        $info = $this->db
            ->from('unproducto')
            ->orderBy("precio.fecha_actualizacion")
            ->select(null)
            ->limit($l)
            ->offset($p)
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
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion
			
			')
            ->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			ON unproducto.precio_id=precio.id")

            ->select(" 
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->where("unproducto.aprobado=1")
            ->where($filtrosAll)
            ->fetchAll();





        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $distancia = $this->harvestine($latitud, $longitud, $unproducto['ubicacion']['latitud'], $unproducto['ubicacion']['longitud']);
            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $unproducto['distancia'] = $distancia;
            $allProductos[] = $unproducto;
        }



        $productos = [
            'data' => $allProductos
        ];

        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");

    }



    public function aprobarProducto($idUnProducto, $idUsuario)
    {

        $set = ['aprobado' => 1];
        $query = $this->db->update($this->table)->set($set)->where('id', $idUnProducto)->execute();
        if ($query) {

            $idUsuarioPostulante = $this->db->from($this->table)
                ->select(null)
                ->select("usuario_postula_precio.usuario_id     AS usuarioSubeProducto")
                ->innerJoin("precio                             ON precio.id= unproducto.precio_id")
                ->innerJoin("usuario_postula_precio             ON usuario_postula_precio.id=precio.usuario_postula_precio_id")
                ->where("unproducto.id", $idUnProducto)
                ->fetch();

            $puntosUsuarioPostulante = $this->db->from("usuario")->where("id", $idUsuarioPostulante->usuarioSubeProducto)->fetch()->puntos;

            $set = ["puntos" => $puntosUsuarioPostulante + 10];
            $this->db->update("usuario", $set, $idUsuarioPostulante->usuarioSubeProducto)->execute();
            
            $puntosUsuarioAprobador = $this->db->from("usuario")->where("id", $idUsuario)->fetch()->puntos;

            $set = ["puntos" => $puntosUsuarioAprobador + 5];
            $this->db->update("usuario", $set, $idUsuario)->execute();





            return $this->response->SetResponse(true, " ", ["exito" => $query,"set"=>$set,"nose"=>$puntosUsuarioAprobador], "001");
        } else {
            return $this->response->SetResponse(true, " ", ["exito" => false], "001");
        }
    }




    public function listarProductosDeSucursal($idSucursal, $l, $p, $distancia)
    {



        $p = $p * $l;
        $info = $this->db
            ->from('unproducto')
            ->orderBy("precio__fecha_actualizacion DESC")
            ->select(null)
            ->limit($l)
            ->offset($p)
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
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion
			
			')
            ->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			ON unproducto.precio_id=precio.id")

            ->where("unproducto.sucursal_id", $idSucursal)
            ->select(" 
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where('unproducto.aprobado=1')
            ->fetchAll();

        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $unproducto['distancia'] = $distancia;
            $allProductos[] = $unproducto;
        }

        $total =
            $this->db
            ->from('unproducto')
            ->select('COUNT(*) Total')
            ->where("sucursal_id", $idSucursal)
            ->fetch()
            ->Total;

        $productos = [
            'data' => $allProductos,
            'cantidaddeproductos' => $total
        ];


        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }




    public function obtenerProducto($id)
    {
        $unproducto = $this->db
            ->from($this->table, $id)
            ->fetch();
        if ($unproducto) {

            $producto = $this->db
                ->from('producto', $unproducto->producto_id)
                ->fetch();

            $precio = $this->db
                ->from('precio', $unproducto->precio_id)
                ->fetch();

            $sucursal = $this->db
                ->from('sucursal', $unproducto->sucursal_id)
                ->fetch();

            $infoCompleta = [
                'ids' => $unproducto,
                'producto' => $producto,
                'precio' => $precio,
                'sucursal' => $sucursal
            ];
            return $this->response->SetResponse(true, " ", ["producto" => $infoCompleta]);
        } else {
            return $this->response->SetResponse(false, "No se encuentra el producto con tal id");
        }
    }





    public function obtenerVerificarProductoDeBarcode($barcode, $sucursalId)
    {

        $existeProducto = $this->db
            ->from('producto')
            ->where('barcode', $barcode)
            ->fetch();

        if ($existeProducto) {

            $existeProductoEnSucural = $this->db
                ->from('unproducto')
                ->innerJoin('producto as unproducto.producto_id=producto.id')
                ->innerJoin('sucursal as unproducto.sucursal_id=sucursal.id')
                ->where('producto.barcode', $barcode)
                ->where('unproducto.sucursal_id', $sucursalId)
                ->fetch();

            if ($existeProductoEnSucural) {
                $productos = [
                    'exite' => true
                ];

            } else {

                $unproducto = $this->db
                    ->from('producto')
                    ->select(null)
                    ->select('
            
                        producto.id                 as producto__id,
                        producto.unidad_id          as producto__unidad_id,
                        producto.categoria_id       as producto__categoria_id,
                        producto.tipoproducto_id    as producto__categoria_id,
                        producto.nombre             as producto__nombre,
                        producto.barcode            as producto__barcode,
                        producto.marca              as producto__marca,
                        producto.image              as producto__image,
                        producto.cantidad           as producto__cantidad,
                        producto.detalles           as producto__detalles,

                        unidad.id                   as unidad__id,
                        unidad.texto                as unidad__texto,

                        categoria.id                as categoria__id,
                        categoria.rubro_id          as categoria__rubro_id,
                        categoria.nombre            as categoria__nombre,

                        tipoproducto.id             as tipoproducto__id,
                        tipoproducto.nombre         as tipoproducto__nombre

                    ')
                    ->where("barcode", $barcode)
                    ->innerJoin("unidad         on producto.unidad_id=unidad.id")
                    ->innerJoin("categoria      on producto.categoria_id=categoria.id")
                    ->innerJoin("tipoproducto   on producto.tipoproducto_id=tipoproducto.id")
                    ->fetch();


                $elproducto = [];
                foreach ((array)$unproducto as $key => $inf) {
                    $porciones = explode('__', $key);
                    $elproducto[$porciones[0]][$porciones[1]] = $inf;
                }
                $productos = [
                    'data' => $elproducto,
                    'exite' => false
                ];

            }

        } else {

            $productos = [
                'exite' => false
            ];
        }


        return $this->response->SetResponse(true, " ", ["producto" => $productos], "001");


















        $existe = $this->db
            ->from('unproducto')
            ->innerJoin('producto as unproducto.producto_id=producto.id')
            ->innerJoin('sucursal as unproducto.sucursal_id=sucursal.id')
            ->where('producto.barcode', $barcode)
            ->where('unproducto.sucursal_id', $sucursalId)
            ->fetch();

        if ($existe) {
            $productos = [
                'existe' => true
            ];
            return $this->response->SetResponse(true, " ", ["producto" => $productos], "001");

        } else {



        }

    }





    public function obtenerProductoDeBarcode($l, $p, $barcode, $latitud, $longitud)
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

        $p = $p * $l;
        $info = $this->db
            ->from('unproducto')
            ->orderBy("precio__fecha_actualizacion DESC")
            ->select(null)
            ->limit($l)
            ->offset($p)
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
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion
			
			')
            ->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			ON unproducto.precio_id=precio.id")

            ->select(" 
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->where('unproducto.aprobado=1')
            ->where("producto.barcode=$barcode")
            ->fetchAll();

        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $distancia = $this->harvestine($latitud, $longitud, $unproducto['ubicacion']['latitud'], $unproducto['ubicacion']['longitud']);
            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $unproducto['distancia'] = $distancia;
            $allProductos[] = $unproducto;
        }


        $productos = [
            'data' => $allProductos
        ];


        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");

    }


    public function postularPrecioParaActualizarProducto($data)
    {
        $precio = $data['precio'];
        $usuarioId = $data['usuario_id'];
        $idPrecio = $data['idPrecio'];
        $fecha = date('Y-m-d H:i:s');

        $valuesUsuarioActualizaPrecio = [
            'usuario_id' => $usuarioId,
            'precio_id' => $idPrecio,
            'precio' => $precio,
            'fecha' => $fecha
        ];
        $idUsuarioActualizaPrecio = $this->db->insertInto('usuario_postula_precio', $valuesUsuarioActualizaPrecio)->execute();

        $infoCompleta = [
            'exito' => $idUsuarioActualizaPrecio
        ];
        return $this->response->SetResponse(true, " ", ["usuariopostulaprecio" => $infoCompleta], "001");
    }

    public function actualizarpreciodeproducto($data)
    {

        $idProducto = $data['idProducto'];
        $idUsuario = $data['idUsuario'];
        $idPrecio = $data['idPrecio'];
        $montoNuevo = $data['montoNuevo'];
        $montoActual = $data['montoActual'];
        $fecha = date('Y-m-d H:i:s');

        if ($montoNuevo > $montoActual) {

            $this->db->update('precio', ['esActual' => 0], $idPrecio)->execute();
            $valuesPrecio = [
                'precio' => $montoNuevo,
                'esActual' => 1
            ];
            $idNuevoPrecio = $this->db->insertInto('precio', $valuesPrecio)->execute();


            $this->db->update('unproducto', ['precio_id' => $idNuevoPrecio], $idProducto)->execute();
            $valuesUserActPre = [
                'usuario_id' => $idUsuario,
                'precio_id' => $idNuevoPrecio,
                'fecha' => $fecha
            ];
            $idNuevoUser_Act_Pre = $this->db->insertInto('usuario_actualiza_precio', $valuesUserActPre)->execute();
            $nuevoPrecio = $this->db->from('precio', $idNuevoPrecio)->fetch();


            $infoCompleta = ['nuevoPrecio' => $nuevoPrecio];
            return $this->response->SetResponse(true, " ", ["precio" => $infoCompleta], "001");
        } else {
            return $this->response->SetResponse(false, "No se encuentra el producto con tal id", [], "0010");
        }
    }



    public function obtenerIDs($id)
    {
        $data = $this->db
            ->from($this->table, $id)
            ->fetch();
        if ($data) {
            return $this->response->SetResponse(true, "", $data, "001");
        } else {
            return $this->response->SetResponse(false, "No se encuentra el producto con tal id", null, "007");
        }
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




    public function productoEnOtrasTiendas($cantidad,$idProductoActual, $barcode, $latitud, $longitud)
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


        $info = $this->db
            ->from('unproducto')
            ->orderBy("precio.fecha_actualizacion")
            ->select(null)
            ->limit($cantidad)
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
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion
			
			')
            ->innerJoin("sucursal 			ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			ON unproducto.precio_id=precio.id")
            
            ->select(" 
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->where("producto.barcode=$barcode")
            ->where("unproducto.id!=$idProductoActual")
            ->fetchAll();

        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $distancia = $this->harvestine($latitud, $longitud, $unproducto['ubicacion']['latitud'], $unproducto['ubicacion']['longitud']);
            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $unproducto['distancia'] = $distancia;
            $allProductos[] = $unproducto;
        }


        $productos = [
            'data' => $allProductos,
            'info' => $info
        ];


        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }



    public function insertarProductoCompleto($data)
    {

        $producto = [
            'unidad_id' => $data['unidad_id'],
            'categoria_id' => $data['categoria_id'],
            'tipoproducto_id' => $data['tipoproducto_id'],
            'nombre' => $data['nombre'],
            'barcode' => $data['barcode'],
            'marca' => $data['marca'],
            'image' => $data['barcode'],
            'cantidad' => $data['cantidad'],
            'detalles' => $data['detalles']
        ];

        $id_producto = $this->db->insertInto('producto', $producto)->execute();


        $precio = [
            'precio' => $data['precio'],
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        $id_precio = $this->db->insertInto('precio', $precio)->execute();

        $usuario_portula_precio = [
            'usuario_id' => $data['usuario_id'],
            'precio_id' => $id_precio,
            'precio' => $data['precio'],
            'fecha' => date('Y-m-d H:i:s')
        ];

        $usuario_postula_precio_id = $this->db->insertInto('usuario_postula_precio', $usuario_portula_precio)->execute();

        $id_sucursal = $data['id_sucursal'];

        $this->db->update('precio', ['usuario_postula_precio_id' => $usuario_postula_precio_id], $id_precio)->execute();

        $unproducto = [
            'sucursal_id' => $id_sucursal,
            'producto_id' => $id_producto,
            'precio_id' => $id_precio,
            'aprobado' => 0
        ];

        $id_unproducto = $this->db->insertInto('unproducto', $unproducto)->execute();

        if ($id_unproducto) {
            return $this->response->SetResponse(true, " ", ["exito" => $id_unproducto], "001");
        } else {
            return $this->response->SetResponse(true, " ", ["exito" => false], "001");
        }
    }

    public function insertarProductoSoloPrecio($data)
    {

        $id_producto =$data['producto_id'];
        
        $precio = [
            'precio' => $data['precio'],
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        $id_precio = $this->db->insertInto('precio', $precio)->execute();

        $usuario_portula_precio = [
            'usuario_id' => $data['usuario_id'],
            'precio_id' => $id_precio,
            'precio' => $data['precio'],
            'fecha' => date('Y-m-d H:i:s')
        ];

        $usuario_postula_precio_id = $this->db->insertInto('usuario_postula_precio', $usuario_portula_precio)->execute();

        $id_sucursal = $data['sucursal_id'];

        $this->db->update('precio', ['usuario_postula_precio_id' => $usuario_postula_precio_id], $id_precio)->execute();

        $unproducto = [
            'sucursal_id' => $id_sucursal,
            'producto_id' => $id_producto,
            'precio_id' => $id_precio,
            'aprobado' => 0
        ];

        $id_unproducto = $this->db->insertInto('unproducto', $unproducto)->execute();

        if ($id_unproducto) {
            return $this->response->SetResponse(true, " ", ["exito" => $id_unproducto], "001");
        } else {
            return $this->response->SetResponse(true, " ", ["exito" => false], "001");
        }
    }



    public function productosPorAprobar($idUsuario, $latitud, $longitud)
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


        $info = $this->db
            ->from('unproducto')
            ->orderBy("precio.fecha_actualizacion")
            ->select(null)
            ->limit(4)
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
			precio.fecha_actualizacion 		AS precio__fecha_actualizacion
			
			')
            ->innerJoin("sucursal 			        ON unproducto.sucursal_id=sucursal.id")
            ->innerJoin("producto 			        ON unproducto.producto_id=producto.id")
            ->innerJoin("precio 			        ON unproducto.precio_id=precio.id")
            ->innerJoin("usuario_postula_precio     ON precio.usuario_postula_precio_id=usuario_postula_precio.id")

            ->select(" 
			ubicacion.latitud				AS ubicacion__latitud, 
			ubicacion.longitud 				AS ubicacion__longitud")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->where("usuario_postula_precio.usuario_id!=$idUsuario")
            ->where("unproducto.aprobado=0")
            ->fetchAll();

        $allProductos = [];
        foreach ($info as $valor) {
            $calificacionFinal = 0;
            $valor = (array)$valor;
            $unproducto = [];
            foreach ($valor as $key => $inf) {
                $porciones = explode('__', $key);
                $unproducto[$porciones[0]][$porciones[1]] = $inf;
            }

            $distancia = $this->harvestine($latitud, $longitud, $unproducto['ubicacion']['latitud'], $unproducto['ubicacion']['longitud']);
            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->select("AVG(usuario_califica_producto.calificacion) as totalfinal")
                ->where('unproducto_id', $unproducto["ids"]["id"])
                ->fetch();

            $unproducto['calificacion'] = ($calificaciones->totalfinal != null) ? round($calificaciones->totalfinal, 1) : 0;
            $unproducto['distancia'] = $distancia;
            $allProductos[] = $unproducto;
        }


        $productos = [
            'data' => $allProductos,
            'info' => $info
        ];


        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }

	
	
	/*
    public function listarProductos($l, $p)
    {
		$start = microtime(true);

        $p = $p * $l;
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->orderBy("fecha_actualizacion")
            ->fetchAll();
        $infoProducto = [];
        foreach ($data as $unproducto) {


            $producto = $this->db
                ->from('producto', $unproducto->producto_id)
                ->fetch();
            $producto->nombre = ucfirst($producto->nombre);

            $precio = $this->db
                ->from('precio', $unproducto->precio_id)
                ->fetch();

            $sucursal = $this->db
                ->from('sucursal', $unproducto->sucursal_id)
                ->fetch();

            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->where('unproducto_id', $unproducto->id)
                ->fetchAll();
            $calificacionFinal = 0;
            foreach ($calificaciones as $calificacion) {
                $calificacionFinal = $calificacionFinal + $calificacion->calificacion;
            }
            $calificacionFinal = (count($calificaciones) > 0) ? $calificacionFinal / (count($calificaciones)) : 0;

            $infoProducto[] = [
                'ids' => $unproducto,
                'producto' => $producto,
                'precio' => $precio,
                'sucursal' => $sucursal,
                'calificacion' => $calificacionFinal
            ];

        }
		$time_elapsed_secs = microtime(true) - $start;
        $productos = [
            'data' => $infoProducto,
			'time'=>$time_elapsed_secs
        ];
		
        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }
	
     */
	
	
	/*
    public function listarProductosPorLocalizacion($l, $p,$latitud,$longitud)
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
		
		$p = $p * $l;
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->orderBy("fecha_actualizacion")
            ->select("sucursal.ubicacion_id ")
            ->innerJoin("sucursal ON unproducto.sucursal_id=sucursal.id")
            ->select(" ubicacion.latitud, ubicacion.longitud ")
            ->innerJoin("ubicacion ON sucursal.ubicacion_id = ubicacion.id")
            ->where("ubicacion.latitud >= :lat_min and ubicacion.latitud  <= :lat_max", $LatParams)
            ->where("ubicacion.longitud >= :lng_min and ubicacion.longitud  <= :lng_max", $LgnParams)
            ->fetchAll();
            
            
			
			
			
        $allProductos = [];
        foreach ($data as $unproducto) {


            $producto = $this->db
                ->from('producto', $unproducto->producto_id)
                ->fetch();
            $producto->nombre = ucfirst($producto->nombre);

            $precio = $this->db
                ->from('precio', $unproducto->precio_id)
                ->fetch();

            $sucursal = $this->db
                ->from('sucursal', $unproducto->sucursal_id)
                ->fetch();
                
            $ubicacion = $this->db
                ->from('ubicacion',$sucursal->ubicacion_id)
                ->fetch();
				
				
            $distancia=$this->harvestine($latitud, $longitud, $ubicacion->latitud, $ubicacion->longitud);
            $calificaciones = $this->db
                ->from('usuario_califica_producto')
                ->where('unproducto_id', $unproducto->id)
                ->fetchAll();
            $calificacionFinal = 0;
            foreach ($calificaciones as $calificacion) {
                $calificacionFinal = $calificacionFinal + $calificacion->calificacion;
            }
            $calificacionFinal = (count($calificaciones) > 0) ? $calificacionFinal / (count($calificaciones)) : 0;

            $allProductos[] = [
                'ids' => $unproducto,
                'producto' => $producto,
                'precio' => $precio,
                'sucursal' => $sucursal,
                'calificacion' => $calificacionFinal,
                'distancia'=>$distancia
            ];

        }
		
		
		
		
        $productos = [
            'data' => $allProductos
        ];
		
		
		
		
		
		
		
		
		
        return $this->response->SetResponse(true, " ", ["productos" => $productos], "001");


    }*/


}