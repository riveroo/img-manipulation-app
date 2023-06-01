<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\ImageManipulation;
use App\Http\Requests\StoreImageManipulationRequest;
use App\Http\Requests\UpdateImageManipulationRequest;
use App\Http\Resources\V1\ImageManupulationResource;
use App\Models\Album;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File ;
use Nette\Utils\Floats;
use Nette\Utils\Strings;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ImageManupulationResource::collection(ImageManipulation::paginate());
    }

    public function byAlbum(Album $album)
    {
        $where = [
            'album_id' => $album->id
        ];
        return ImageManupulationResource::collection(ImageManipulation::where($where)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImageManipulationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(StoreImageManipulationRequest $request)
    {
        $all = $request->all();

        $image = $all['image'];
        unset($all['image']);

        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => null,
        ];

        if (isset($all['album_id'])) {
            //TODO

            $data['album_id']= $all['album_id'];

        }

        $dir = 'images/'.Str::random().'/';
        $absolutPath = public_path($dir);
        File::makeDirectory($absolutPath);

        //if image save as file
        if ($image instanceof UploadedFile) {
            $data['name'] = $image->getClientOriginalName();
            // change test.jpg to test-resize.jpg
            $filename = pathinfo($data['name'],PATHINFO_FILENAME);
            $extention = $image->getClientOriginalExtension();
            $originalPath = $absolutPath.$data['name'];

            $image->move($absolutPath,$data['name']);
           

        } 
        //Image is URL
        else {
            $data['name'] = pathinfo($image,PATHINFO_BASENAME);
            $filename = pathinfo($image,PATHINFO_FILENAME);
            $extention = pathinfo($image,PATHINFO_EXTENSION);
            $originalPath = $absolutPath.$data['name'];

            copy($image,$originalPath);
        }
        $data['path'] = $dir.$data['name'];

        $w = $all['w'];
        $h = $all['h'] ?? false;

        list($width , $height, $image) = $this->getImageWidthAndHeight($w,$h,$originalPath);

        $resizeImageName = $filename.'-resized.'.$extention;

        $image->resize($width,$height)->save($absolutPath.$resizeImageName);
        $data['output_path'] = $dir.$resizeImageName;

        $imageManipulation = ImageManipulation::create($data);

        return response()->json([
            'Status' => 'success',
            'RC'=>'200',
            'message' => new ImageManupulationResource($imageManipulation) 
        ]);
      

        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $image)
    {
        return new ImageManupulationResource($image);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $image)
    {
        $image->delete();
        return response()->json([
            'status' => 'image has been deleted',
            'RC' => '204',
        ]);
       
        
    }

    protected function getImageWidthAndHeight($w,$h,string $originalPath)
    {
        $image = Image::make($originalPath);
        $OriginalWidth = $image->width();
        $OriginalHeight = $image->height();

        if (str_ends_with($w,'%')) {
            $ratioW = (float)str_replace('%','',$w);
            $ratioH = $h ? (float)str_replace('%','',$h) : $ratioW;

            $newWidth = $OriginalWidth * $ratioW / 100;
            $newHeight = $OriginalHeight * $ratioH /100;
        } else {
            $newWidth = (float)$w;

            $newHeight = $h ? (float)$h : $OriginalHeight*$newWidth/$OriginalWidth;
        }

        return [$newWidth,$newHeight,$image];

    }
}
