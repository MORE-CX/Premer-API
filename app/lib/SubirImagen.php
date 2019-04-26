<?php
namespace App\Lib;

use Slim\Http\UploadedFile;

class SubirImagen
{
    public $dir;
    public $uploadedFile;
    public $name;
    
    public function __construct(string $name, string $dir, UploadedFile $uploadedFile)
    {
        $this->name = $name;
        $this->uploadedFile = $uploadedFile;
        $this->dir = $dir;
        return $this;
    }

    public function subirImagenLogin()
    {
            $extension = pathinfo($this->uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $this->name.".jpg";
            $local= DIRECTORY_SEPARATOR .'usuario'. DIRECTORY_SEPARATOR . $filename;
            $this->uploadedFile->moveTo($this->dir .$local);
            
        return $this;
    }


    public function subirImagenProducto()
    {
            $extension = pathinfo($this->uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $this->name.".jpg";
            $local= DIRECTORY_SEPARATOR .'producto'. DIRECTORY_SEPARATOR . $filename;
            $this->uploadedFile->moveTo($this->dir .$local);
            
        
    }


}