<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/precio/', function () {

    $this->get('listarcanastas/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->precio->listarcanastas($args['lim'], $args['pag'])));
    });

    $this->post('actualizarprecio', function ($req, $res, $args) { 
        $infoActualizacion=$req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->precio->actualizarPrecio($infoActualizacion)));    
    });

})->add(new AuthMiddleware($app));