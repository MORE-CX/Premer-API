<?php
namespace App\Model;

use App\Lib\Response;

class UsuarioModel
{
    private $db;
    private $table = 'usuario';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }

    public function listar($l, $p)
    {
        $data =
            $this->db->from($this->table)
            ->limit($l)
            ->offset($p)
            ->select($this->table . '.*, rol.nombre as rol')
            ->orderBy("id DESC")
            ->fetchAll();

        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;
        $users = [
            'data' => $data,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["usuarios" => $users], "001");

    }

    public function registrar($data)
    {
        $data['password'] = md5($data['password']);
        $id = $this->db->insertInto($this->table, $data)
            ->execute();
        if ($id) {
            $usuario = $this->db
                ->from($this->table, $id)
                ->fetch();
            return $this->response->SetResponse(true, "", $usuario, "001");
        } else {
            return $this->response->SetResponse(false, "No se pudo registrar", "006");
        }

    }

    public function obtener($id)
    {
        $usuario = $this->db
            ->from($this->table, $id)
            ->select(null)
            ->select('
            usuario.id          as id,
            usuario.email       as email,
            usuario.nombre      as nombre,
            usuario.image       as image,
            usuario.facebook    as facebook,
            usuario.google      as google,
            usuario.normal      as normal,
            usuario.puntos      as puntos,
            estado.nombre       as estado
            ')
            ->innerJoin('estado on estado.id=usuario.estado_id')
            ->fetch();
        if ($usuario) {
            return $this->response->SetResponse(true, " ", $usuario, "001");
        } else {
            return $this->response->SetResponse(false, "No se encuentra el usuario con tal id", null, "007");
        }
    }

    public function eliminar($id)
    {
        $rta = $this->db->deleteFrom($this->table, $id)
            ->execute();

        if ($rta) {
            return $this->response->SetResponse(true, "", null, "001");
        } else {
            return $this->response->SetResponse(false, "No se encuentra el usuario con tal id", null, "007");
        }
    }


    public function actualizar($data, $id)
    {
        if (isset($data['password'])) {
            $data['password'] = md5($data['password']);

            $usuario = $this->db->from($this->table)
                ->where("id", $id)
                ->fetch();

            if (md5($data['passwordOld']) == $usuario->password) {
                $rta = $this->db->update($this->table, ['password' => $data['password']], $id)
                    ->execute();
                return $this->response->SetResponse(true, "Actualizado correctamente", null, "001");
            } else {
                return $this->response->SetResponse(false, "Password ingresado no coincide con el antiguo.", null, "0012");
            }


        }
        $rta = $this->db->update($this->table, $data, $id)
            ->execute();

        if ($rta) {
            return $this->response->SetResponse(true, "Actualizado correctamente", null, "001");
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
            return $this->response->SetResponse(false, "No se encuentra el usuario con tal id", null, "007");
        }
    }

    public function dataAdministrador($id)
    {
        $usuario = $this->db
            ->from($this->table)
            ->where("id", $id)
            ->fetch();

        $cantPreciosPostulados = $this->db
            ->from('usuario_postula_precio')
            ->select('count(*) as Total')
            ->where("usuario_id", $id)
            ->fetch()
            ->Total;

        $cantProductosAgregados = $this->db
            ->from('(
                SELECT id
                FROM usuario_postula_precio
                WHERE usuario_id=1
                GROUP BY precio_id
                ORDER BY id ASC
                ) as m')
            ->select(null)
            ->select('COUNT(*) as Total')
            ->fetch()
            ->Total;


        $cantProductosPendientes = $this->db
            
            ->from('unproducto')
            ->select('COUNT(*) Total')
            ->innerJoin("precio on precio.id=unproducto.precio_id")
            ->innerJoin("usuario_postula_precio on precio.id=usuario_postula_precio.precio_id")
            ->where("usuario_id=1 and aprobado=0")
            ->fetch()
            ->Total;


        $nroRango = 0;
        $puntos = $usuario->puntos;
        $rango = "Sin rango";
        if ($puntos >= 100 && $puntos < 150) {
            $rango = 'Principiante Novato';
            $nroRango = 1;
        }
        if ($puntos >= 150 && $puntos < 200) {
            $rango = 'Principiante Experto';
            $nroRango = 2;
        }
        if ($puntos >= 200 && $puntos < 400) {
            $rango = 'Superior Novato';
            $nroRango = 3;
        }
        if ($puntos >= 400 && $puntos < 600) {
            $rango = 'Superior Experto';
            $nroRango = 4;
        }
        if ($puntos >= 600 && $puntos < 900) {
            $rango = 'Master Novato';
            $nroRango = 5;
        }
        if ($puntos >= 900 && $puntos < 1300) {
            $rango = 'Master Experto';
            $nroRango = 6;
        }
        if ($puntos >= 1300) {
            $rango = 'Dios sobre todos los hombres';
            $nroRango = 7;
        }


        $data = [
            'data' => [
                'cantPreciosPostulados' => $cantPreciosPostulados,
                'cantProductosAgregados' => $cantProductosAgregados,
                'cantProductosPendientes' => $cantProductosPendientes,
                'rango' => $rango,
                'puntos' => $puntos,
                'nroRango' => $nroRango
            ]
        ];


        return $this->response->SetResponse(true, " ", ["usuario" => $data], "001");
    }


    public function actualizarUsuario($data, $id)
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


}