<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Http\Resources\V1\AlbumResource;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return AlbumResource::collection(Album::where('user_id',$request->user()->id)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAlbumRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlbumRequest $request)
    {
        try {
            $data = $request->all();
            $data['user_id'] = $request->user()->id;
            $album = Album::create($data);

        return new AlbumResource($album) ; 
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'Store Failed',
                'respon code' => '410',
                'data' => $th
            ],200);
        }
        
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,Album $album)
    {
        if ($request->user()->id != $album->user_id) {
            return abort(403,'unauthorize request');
        }
        return new AlbumResource($album) ;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAlbumRequest  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        try {
            if ($request->user()->id != $album->user_id) {
                return abort(403,'unauthorize request');
            }
            $album -> update($request->all());

            return response()->json([
                'status' => 'update success',
                'respon code' => '200',
                'data' => $album
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'update Failed',
                'respon code' => '410',
                'data' => $album
            ],410);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Album $album)
    {
        if ($request->user()->id != $album->user_id) {
            return abort(403,'unauthorize request');
        }
        $album->delete();

        return response()->json([
            'status' => 'delete success',
            'respon code' => '200',
            'data' => $album
        ],200);
    }
}
