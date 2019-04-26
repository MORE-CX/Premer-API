<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/categoria/', function () {

    $this->get('listarcategorias', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->categoria->listarCategorias()));
    });

    $this->get('obtenercategoria/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->categoria->obtenerCategoria($args['id'])));
    });


})->add(new AuthMiddleware($app));