<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/tipoproducto/', function () {

    $this->get('listartiposdeproductos', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->tipoproducto->listarTiposDeProductos()));
    });

    

})->add(new AuthMiddleware($app));