<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/ubicacion/', function () {

    $this->get('listarubicaciones/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->ubicacion->listarubicaciones($args['lim'], $args['pag'])));
    });

    $this->get('obtenerubicacion/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->ubicacion->obtenerubicacion($args['id'])));
    });


})->add(new AuthMiddleware($app));