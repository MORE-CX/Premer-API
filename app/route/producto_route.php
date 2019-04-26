<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/producto/', function () {

    $this->get('listarproductos/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->listarProductos($args['lim'], $args['pag'])));
    });
	
	/*
	$this->get('listarproductoslite/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->listarProductoslite($args['lim'], $args['pag'])));
    });
    */
	
	$this->post('votarporprecio', function ($req, $res, $args) {
		$info = $req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->votarPorPrecio($info['idPrecio'],$info['idUsuario'])));
    });
	
	
	$this->post('postularprecioparaactualizarproducto', function ($req, $res, $args) {
		$info = $req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->postularPrecioParaActualizarProducto($info)));
    });
	
	$this->get('usuariosquequierenactualizarprecio/{idPrecio}/{idUsuario}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->usuariosQueQuierenActualizarPrecio($args['idPrecio'],$args['idUsuario'])));
    });
    
    $this->get('listarproductosporlocalizacion/{lim}/{pag}/{lat}/{long}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->listarproductosporlocalizacion($args['lim'], $args['pag'],$args['lat'],$args['long'])));
    });
    

    $this->get('listarids/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->listarids($args['lim'], $args['pag'])));
    });


    $this->get('listarproductosdesucursal/{lim}/{pag}/{idsucursal}/{distancia}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->listarproductosdesucursal($args['idsucursal'], $args['lim'], $args['pag'],$args['distancia'])));
    });
    
    $this->get('productosporaprobar/{idUsuario}/{lat}/{long}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->productosPorAprobar($args['idUsuario'],$args['lat'],$args['long'])));
    });
    

    $this->get('productoenotrastiendas/{cantidad}/{idProductoActual}/{codigoBarra}/{lat}/{long}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->productoenotrastiendas($args['cantidad'],$args['idProductoActual'], $args['codigoBarra'], $args['lat'], $args['long'])));
    });
    
    $this->get('obtenerverificarproductodebarcode/{codigoBarra}/{idSucursal}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->obtenerverificarproductodebarcode($args['codigoBarra'],$args['idSucursal'])));
    });

    $this->post('busquedadeproducto/{lim}/{pag}/{lat}/{long}', function ($req, $res, $args) {
        $filtros = $req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->busquedaDeProductos($args['lim'], $args['pag'], $filtros,$args['lat'],$args['long'])));
    });

    
    $this->post('insertarproductocompleto', function ($req, $res, $args) {
        $data = $req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->insertarProductoCompleto($data)));
    });
    
    $this->post('insertarproductosoloprecio', function ($req, $res, $args) {
        $data = $req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->insertarProductoSoloPrecio($data)));
    });

    

    $this->post('aprobarproducto/{idUnProducto}/{idUsuario}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->aprobarProducto($args['idUnProducto'],$args['idUsuario'])));
    });

    
    $this->post('uploadimage/{id}', function ($req, $res, $args) {
        $id = $args['id'];
        $dir = $this->get('up_dir');
        $imagen = $req->getUploadedFiles()['image'];
        $uploadImg = new SubirImagen($id, $dir, $imagen);
        $uploadImg->subirImagenProducto();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($uploadImg));
    });

    
    $this->post('actualizarpreciodeproducto', function ($req, $res, $args) {
        $data = $req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->actualizarpreciodeproducto($data)));
    });



    $this->get('obtenerproducto/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->obtenerproducto($args['id'])));
    });

    $this->get('obtenerids/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->obtenerids($args['id'])));
    });

    $this->get('obtenerproductodebarcode/{lim}/{pag}/{barcode}/{lat}/{long}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->producto->obtenerProductoDeBarcode($args['lim'], $args['pag'], $args['barcode'],$args['lat'],$args['long'])));
    });
    
    

})->add(new AuthMiddleware($app));