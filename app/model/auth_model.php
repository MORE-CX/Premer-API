<?php
namespace App\Model;

use Firebase\JWT\JWT,
    App\Lib\Response,
    App\Lib\Auth,
    App\Lib\Email;

class AuthModel
{
    private $db;
    private $table = 'usuario';
    private $response;
    private $default_rol_id = 4;
    private $default_estado_id = 2;
    private $default_image = "https://premersite.000webhostapp.com/public/imagenes/default/user-image.png";
    private static $secret_key = 'Tomate11@';
    private static $encrypt = ['HS256'];

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }

    public function autenticar($email, $password)
    {
        $usuario = $this->db->from($this->table)
            ->where('email', $email)
            ->fetch();

        if (is_object($usuario)) {
            if ($usuario->estado_id != $this->default_estado_id) {
                if ($usuario->normal != null) {
                    if ($usuario->password == md5($password)) {
                        $token = Auth::SignIn([
                            'id' => $usuario->id,
                            'nombre' => $usuario->nombre,
                            'image' => $usuario->image,
                            'email' => $usuario->email,
                            'puntos' => $usuario->puntos,
                            'rol_id' => $usuario->rol_id
                        ]);
                        return $this->response->SetResponse(true, "Exito al autenticar", ["token" => $token], "001");
                    } else {
                        return $this->response->SetResponse(false, 'Contraseña incorrecta', null, "002");
                    }
                } else {

                    $data["password"] = Email::sendEmailDefinirPassword($email);
                    $data["normal"] = 1;
                    $this->db->update($this->table, $data, $usuario->id)->execute();
                    return $this->response->SetResponse(false, 'Cuenta registrada pero no tiene password definido, se ha enviado un correo al email, con un nuevo password para usar la cuenta.', null, "003");

                }
            } else {

                $data["activ_code"] = Email::sendEmailActivacion($email);
                $this->db->update($this->table, $data, $usuario->id)->execute();
                return $this->response->SetResponse(false, 'Aun debe activarse la cuenta. Ha sido enviado un correo a su email para activar la cuenta.', null, "004");
            }
        } else {
            return $this->response->SetResponse(false, 'Email no valido o no se encuentra registrado', null, "005");
        }
    }


    public function obtener($id)
    {
        $usuario = $this->db
            ->from($this->table, $id)
            ->fetch();
        if ($usuario) {
            return $this->response->SetResponse(true, "", $usuario, "001");
        } else {
            return $this->response->SetResponse(false, "No se encuentra el usuario con tal id", null, "007");
        }
    }


    public function autenticarAutomatico($email, $image, $nombre, $tipo)
    {

        $usuario = $this->db->from($this->table)
            ->where('email', $email)
            ->fetch();

        if (is_object($usuario)) {


            $url = $image;
            $img = "imagenes/usuario/$usuario->id.jpg";
            file_put_contents($img, file_get_contents($url));


            $token = Auth::SignIn([
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'image'=>$usuario->image,
                'puntos' => $usuario->puntos,
                'rol_id' => $usuario->rol_id
            ]);
            $data["estado_id"] = 1;
            $this->db->update($this->table, $data, $usuario->id)->execute();
            $id = $usuario->id;



        } else {
            $data = [
                "email" => $email,
                "nombre" => $nombre,
                "image" => $image,
                "rol_id" => $this->default_rol_id,
                "estado_id" => 1
            ];
            $id = $this->registrar($data);

            $usuario = $this->db->from($this->table)
                ->where('email', $email)
                ->fetch();
            $token = Auth::SignIn([
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'image' => $usuario->image,
                'email' => $usuario->email,
                'puntos' => $usuario->puntos,
                'rol_id' => $usuario->rol_id
            ]);

        }
        $this->db->update($this->table, [$tipo => 1], $id)->execute();
        return $this->response->SetResponse(true, "Exito al autenticar", ["token" => $token], "001");
    }

    public function autenticarInvitado()
    {
        $token = Auth::SignIn([
            'rol_id' => 5
        ]);
        return $this->response->SetResponse(true, "Inicio como invitado", ["token" => $token], "001");

    }

    public function registrar($data)
    {
        $id = $this->db->insertInto($this->table, $data)
            ->execute();
        return $id;
    }



    public function registroManual($data)
    {
        $data['password'] = md5($data['password']);
        $data["rol_id"] = $this->default_rol_id;
        $data['estado_id'] = $this->default_estado_id;
        $data['image'] = $this->default_image;
        unset($data["provider"]);
        $data["activ_code"] = Email::sendEmailActivacion($data['email']);
        $id = $this->db->insertInto($this->table, $data)
            ->execute();
        if ($id) {
            $usuario = $this->db
                ->from($this->table, $id)
                ->fetch();
            $this->db->update($this->table, ["normal" => 1,"image"=>$id], $id)->execute();

            $usuario->password = null;
            $usuario->facebook = null;
            $usuario->google = null;
            $usuario->activ_code = null;
            $usuario->normal = null;
            return $this->response->SetResponse(true, "Exito al registrarse", $usuario, "001");
        } else {
            return $this->response->SetResponse(false, "No se pudo registrar", null, "006");
        }
    }

    
    public function actualizar($data, $id)
    {
        $rta = $this->db->update($this->table, $data, $id)
            ->execute();
        $usuario = $this->db
            ->from($this->table, $id)
            ->fetch();
        $usuario->password = null;
        $usuario->facebook = null;
        $usuario->google = null;
        $usuario->activ_code = null;
        $usuario->normal = null;

        if ($rta) {
            return $this->response->SetResponse(true, 'Exito al registrarse', $usuario, "001");
        } else {
            return $this->response->SetResponse(false, "No se encuentra el usuario con tal id", null, "007");
        }
    }

    public function existe($email)
    {
        $usuario = $this->db
            ->from($this->table)
            ->where("email", $email)
            ->fetch();
        if ($usuario) {
            return $this->response->SetResponse(true, "", true, "001");
        } else {
            return $this->response->SetResponse(false, "No existe usuario con tal id", false, "007");
        }
    }

    public function activar($token)
    {
        $tokeDecod = JWT::decode(
            $token,
            self::$secret_key,
            self::$encrypt
        );

        $email = $tokeDecod->email;
        $activ_code = $tokeDecod->activ_code;

        $usuario = $this->db
            ->from($this->table)
            ->where("email", $email)
            ->fetch();
        if ($usuario) {
            if ($usuario->activ_code == $activ_code) {
                $data["estado_id"] = 1;
                $this->db->update($this->table, $data, $usuario->id)
                    ->execute();
                return $this->response->SetResponse(true, "Usuario activado correctamente", null, "001");
            } else {
                return $this->response->SetResponse(false, "Codigo de activacion no valido", null, "008");

            }

        } else {
            return $this->response->SetResponse(false, "No existe usuario con tal email", $email, "009");
        }
    }

    public function localizar($ip)
    {
        $homepage = file_get_contents('http://ip-api.com/json/' . $ip);
        return $this->response->SetResponse(true, " ", ["localizacion" => json_decode($homepage)], "001");
    }


    public function pruebas($data)
    {
        $homepage = file_get_contents('http://ip-api.com/json/' . $data["ip"]);
        $data = [
            'data' => json_decode($homepage)
        ];

        return $this->response->SetResponse(true, " ", ["productos" => $data], "001");
    }





















}