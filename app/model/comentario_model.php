<?php
namespace App\Model;

use App\Lib\Response;

class ComentarioModel
{
    private $db;
    private $table = 'comentario';
    private $response;

    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
    }




    public function listarComentariosDeProducto($l, $p, $idProducto)
    {
        $p = $p * $l;
        $data =
            $this->db->from($this->table)
            ->where("unproducto_id", $idProducto)
            ->limit($l)
            ->offset($p)
            ->orderBy("fecha")
            ->fetchAll();
        $comentarios = [];
        foreach ($data as $uncomentario) {
            $usuario = $this->db->from('usuario', $uncomentario->usuario_id)
                ->fetch();

            $user = [
                'nombre' => $usuario->nombre,
                'image' => $usuario->image,
                'id' => $usuario->id
            ];

            $comentarios[] = [
                'usuario' => $user,
                'comentario' => $uncomentario
            ];
        }

        $total =
            $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;

        $todosLosComentarios = [
            'data' => $comentarios,
            'total' => $total
        ];
        return $this->response->SetResponse(true, " ", ["comentarios" => $todosLosComentarios], "001");


    }



    public function comentariounproducto($infoComentario)
    {

        $values = [
            'usuario_id' => $infoComentario['usuario_id'],
            'unproducto_id' => $infoComentario['unproducto_id'],
            'texto' => $infoComentario['texto'],
            'fecha' => date('Y-m-d H:i:s')
        ];

        $idComentario = $this->db->insertInto($this->table, $values)->execute();
        $uncomentario = $this->db->from($this->table, $idComentario)
            ->fetch();
        $usuario = $this->db->from('usuario', $infoComentario['usuario_id'])
            ->fetch();
        $user = [
            'nombre' => $usuario->nombre,
            'image' => $usuario->image,
            'id' => $usuario->id
        ];
        $comentario = [
            'usuario' => $user,
            'comentario' => $uncomentario
        ];
        $comentario = [
            'data' => $comentario
        ];
        return $this->response->SetResponse(true, " ", ["comentario" => $comentario], "001");


    }


}