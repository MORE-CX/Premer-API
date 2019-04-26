<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/sucursal/', function () {

    $this->get('listarsucursales/{lim}/{pag}/{lat}/{long}/{nivel}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->sucursal->listarsucursales($args['lim'], $args['pag'],$args['lat'],$args['long'],$args['nivel'])));
    });

    $this->get('obtenersucursal/{idSucursal}/{distancia}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->sucursal->obtenersucursal($args['distancia'],$args['idSucursal'])));
    });


})->add(new AuthMiddleware($app));