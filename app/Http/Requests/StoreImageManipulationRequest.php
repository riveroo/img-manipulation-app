<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Psy\VarDumper\Dumper;

class StoreImageManipulationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'image' => ['required'],
            'w' => ['required','regex:/^\d+)?%?$/'], // 50,50%,50.123,50.123%
            'h' => 'regex:/^\d+)?%?$/',
            'album_id' => 'exist:\app\Models\Album,id'
        ];

        $image = $this->all()['image'] ?? false;
       

        if ($image && $image instanceof UploadedFile) {
           $rules['image'][] = 'image';
        } else {
            $rules['image'][] = 'url';
        }


        return $rules;
    }
}
