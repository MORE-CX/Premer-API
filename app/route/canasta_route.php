<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/canasta/', function () {

    $this->get('listarcanastas/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->canasta->listarcanastas($args['lim'], $args['pag'])));
    });

    $this->get('listarmiscanastas/{lim}/{pag}/{idUsuario}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->canasta->listarmiscanastas($args['lim'], $args['pag'],$args['idUsuario'])));
	});
	$this->get('listarmiscanastassicontieneproducto/{lim}/{pag}/{idUsuario}/{idProducto}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->canasta->listarMisCanastasSiContieneProducto($args['lim'], $args['pag'],$args['idUsuario'],$args['idProducto'])));
    });
    
$this->get('listarproductosdecanasta/{idCanasta}/{latitud}/{longitud}', function ($req, $res, $args) {        
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->listarproductosdecanasta($args['idCanasta'],$args['latitud'],$args['longitud'])));    
    });

    $this->post('agregarproductoacanasta', function ($req, $res, $args) { 
        $infoProducto=$req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->agregarproductoacanasta($infoProducto)));    
    });


    $this->post('quitarproductodecanasta', function ($req, $res, $args) { 
        $infoProducto=$req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->quitarproductodecanasta($infoProducto)));    
    });
    
    
    $this->post('actualizarproductodecanasta', function ($req, $res, $args) { 
        $infoProducto=$req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->actualizarproductodecanasta($infoProducto)));    
    });


    $this->post('crearcanasta', function ($req, $res, $args) { 
        $infoCanasta=$req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->crearcanasta($infoCanasta)));    
    });
	
	
    $this->get('crearcanastabasica/{idUsuario}/{latitud}/{longitud}', function ($req, $res, $args) { 
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->crearcanastabasica($args['idUsuario'],$args['latitud'],$args['longitud'])));    
    });
    
     $this->get('eliminarcanasta/{idCanasta}', function ($req, $res, $args) {        
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->canasta->eliminarcanasta($args['idCanasta'])));    
    });
    

})->add(new AuthMiddleware($app));