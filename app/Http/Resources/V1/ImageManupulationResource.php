<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL as FacadesURL;
use PharIo\Manifest\Url;

class ImageManupulationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'original' => FacadesURL::to($this->path),
            'output_path' => FacadesURL::to($this->output_path),
            'album_id' => $this->album_id,
            'created_at' => $this->created_at,
        ];

       
    }
}
