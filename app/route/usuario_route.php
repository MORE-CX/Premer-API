<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

$app->group('/usuario/', function () {

    $this->get('listar/{lim}/{pag}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->usuario->listar($args['lim'], $args['pag'])));
    });


    $this->get('obtener/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->usuario->obtener($args['id'])));
    });

    $this->get('dataadministrador/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->usuario->dataadministrador($args['id'])));
    });


    
    $this->post('uploadimage/{id}', function ($req, $res, $args) {
        $id = $args['id'];
        $dir = $this->get('up_dir');
        $imagen = $req->getUploadedFiles()['image'];
        $uploadImg = (new SubirImagen($id, $dir, $imagen))->subirImagenLogin();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode(json_encode($uploadImg)));
    });


    

    $this->post('actualizar/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->usuario->actualizar($req->getParsedBody(), $args['id'])));
    });

    $this->delete('eliminar/{id}', function ($req, $res, $args) {
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->model->usuario->eliminar($args['id'])));
    });




    

})->add(new AuthMiddleware($app));