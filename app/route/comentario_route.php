<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/comentario/', function () {

    $this->get('listarcomentariosdeproducto/{lim}/{pag}/{idComentario}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->comentario->listarcomentariosdeproducto($args['lim'], $args['pag'], $args['idComentario'])));
    });

    $this->post('comentariounproducto', function ($req, $res, $args) { 
        $infoComentario=$req->getParsedBody();
        return $res->withHeader('Content-type', 'application/json')
        ->write(json_encode($this->model->comentario->comentariounproducto($infoComentario)));    
    });

})->add(new AuthMiddleware($app));