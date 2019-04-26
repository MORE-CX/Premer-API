<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/beneficio/', function () {

    $this->get('listarbeneficios/{lim}/{pag}/{puntos}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->beneficio->listarbeneficios($args['lim'], $args['pag'], $args['puntos'])));
    });
    
})->add(new AuthMiddleware($app));