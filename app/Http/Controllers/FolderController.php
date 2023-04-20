<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\File;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id=0)
    {
        if($id){
            return auth()->user()->folders()->find($id);
        }
        return auth()->user()->folders;
    }

    /**
     * Show the form for creating a new resource.
     */ 
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|unique:folders',
        ]);

        
        // Get the authenticated user
        $user = auth()->user();

        // Create the new folder in the database
        $folder = new Folder([
            'folder_name' => $request->folder_name,
            'user_id' => $user->id,
            'folder_size'=> 0.0,
        ]);

        $folder->save();

        // Update the number_of_folders of the user
        $user->number_of_folders += 1 ;
        $user->save();

        return response([
            'message' => 'Folder created successfully.',
            'folder' => $folder,
        ], 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Folder $folder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Folder $folder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);
        $user = $folder->user;

        // delete all files in the folder and the folder itself from the database
        $files = File::where('folder_id', $id)->get();
        foreach ($files as $file) {
            Storage::delete($file->file_link);
            $file->delete();
            $user->number_of_files -=1;
        }
        $folder->delete();

        // update user's used storage
        $usedStorage = $user->files->sum('file_size');
        $user->used_storage = $usedStorage;
        $user->number_of_folders -=1;
        $user->save();

        // delete folder from the server
        $folderPath = 'public/backendfiles/' . $user->id . '/' . $id;
        Storage::deleteDirectory($folderPath);

        return response([
            'message' => 'Folder deleted successfully'
        ], 200);
    }
}
