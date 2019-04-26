<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\SubirImagen,
    App\Validation\TestValidation,
    App\Validation\UsuarioValidation,
    App\Middleware\AuthMiddleware;

	

	
$app->group('/auth/', function () {


    $this->get('activate/{code}', function ($req, $res, $args) {
        $data=$this->model->auth->activar($args['code']);
        if($data->errorCode=="001"){
            $url="https://localhost:4200/activacion/1";
        }else{
           $url="https://localhost:4200/activacion/".$data->message;
        }
        return $res->withRedirect($url);
    });
	
	$this->get('localizar/{ip}', function ($req, $res, $args) {
        $data=$this->model->auth->localizar($args['ip']);
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($data));
    });
	

    $this->post('uploadimage/{id}', function ($req, $res, $args) {
        $id = $args['id'];
        $dir = $this->get('up_dir');
        $imagen = $req->getUploadedFiles()['image'];
        $uploadImg = (new SubirImagen($id, $dir, $imagen))->subirImagenLogin();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($uploadImg));
    });
    
    
    
    $this->post('uploadimageproducto/{id}', function ($req, $res, $args) {
        $id = $args['id'];
        $dir = $this->get('up_dir');
        $imagen = $req->getUploadedFiles()['image'];
        $uploadImg = new SubirImagen($id, $dir, $imagen);
        $uploadImg->subirImagenProducto();
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($uploadImg));
    });

	
    $this->post('pruebas', function ($req, $res, $args) {
        $parametros = $req->getParsedBody();
		$auth = $this->model->auth->pruebas($parametros);
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($auth));
    });



    $this->post('login', function ($req, $res, $args) {
        $parametros = $req->getParsedBody();
        $tipoInicio = $parametros['provider'];


        switch ($tipoInicio) {
            case "google":
                $email = $parametros['email'];
                $image = $parametros['image'];
                $nombre = $parametros['nombre'];
                $auth = $this->model->auth->autenticarAutomatico($email, $image, $nombre, $tipoInicio);
                break;
            case "facebook":
                $email = $parametros['email'];
                $image = $parametros['image'];
                $nombre = $parametros['nombre'];
                $auth = $this->model->auth->autenticarAutomatico($email, $image, $nombre, $tipoInicio);
                break;
            case "normal":
                $email = $parametros['email'];
                $password = $parametros['password'];
                $auth = $this->model->auth->autenticar($email, $password);
                break;
            case "invitado":
                $auth = $this->model->auth->autenticarInvitado();
                break;
        }


        return $res->withHeader('Content-type', 'application/json')
		->withHeader('Access-Control-Allow-Origin', 'http://localhost:8100')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->write(json_encode($auth));
    });


    $this->post('register', function ($req, $res, $args) {
        $r = UsuarioValidation::validate($req->getParsedBody());
        $email = $req->getParsedBody()["email"];
        $responseExiste = $this->model->auth->existe($email);

        if ($responseExiste->data) {
            $r->response = false;
            $r->errors["email"][] = "El email ya se encuentra registrado";
        }

        if (!$r->response) {
            return $res->withHeader('Content-type', 'application/json')
                ->withStatus(422)
                ->write(json_encode($r->errors));
        }

        $usuario = $this->model->auth->registroManual($req->getParsedBody());
        return $res->withHeader('Content-type', 'application/json')
            ->write(json_encode($usuario));
    });

});