<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/descuento/', function () {

    $this->get('listardescuentos/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->descuento->listardescuentos($args['lim'], $args['pag'])));
    });
    
    $this->get('listardescuentodesucursal/{lim}/{pag}/{idsucursal}', function ($req, $res, $args) {        
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->descuento->listardescuentodesucursal($args['idsucursal'],$args['lim'],$args['pag'])));    
    });


    $this->get('obtenerdescuento/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->descuento->obtenerdescuento($args['id'])));
    });
})->add(new AuthMiddleware($app));