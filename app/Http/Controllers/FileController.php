<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Folder;
use App\Models\File;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id=0)
    {
        $user = auth()->user();
        $folders = $user->folders()->pluck('id');
    
        $files = DB::table('files')
            ->whereIn('folder_id', $folders)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($file) {
                $created_at = Carbon::parse($file->created_at);
                if ($created_at->isToday()) {
                    return 'today';
                } else if ($created_at->isYesterday()) {
                    return 'yesterday';
                } else {
                    return 'others';
                }
            });

        $file= File::find($id);
        if($id) return $file;

        return $files;
    }

    public function viewFile(Request $request, $fileId)
    {
        $file = File::find($fileId);

        $path = storage_path('app/' . $file->file_link);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        $headers = [
            'Content-Type' => $mimeType,
        ];

        return response()->file($path, $headers);
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
         // Validate the request data
         $validator = Validator::make($request->all(), [
            "file_name"=>"required|unique:files",
            'file' => 'required|file',
            'folder_id' => 'required|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        // Get the authenticated user
        $user = auth()->user();
        

        // Check if the user is allowed to upload more files based on their used storage
        $currentfile_size=$request->file->getSize();
        if ($user->used_storage + $currentfile_size > $user->total_storage) {
            return response()->json([
                'message' => 'storage limit exceeded',
                "storage_used"=>$user->used_storage,
                "intended_to_Use"=>$user->used_storage + $currentfile_size 
            ],400);
        }

        // // Store the file in the storage disk

        $fileLink = $request->file('file')->storeAs(
            'public/backendfiles/'.$user->id . "/" . $request->folder_id,
            $request->file_name.'.'. $request->file('file')->getClientOriginalExtension());
        $url = asset('storage/' . str_replace('public/', '', $fileLink));

        // // Create a new file in the database
        $file = new File([
            "file_name"=>$request->file_name .".".  $request->file('file')->getClientOriginalExtension(),
            'file_link' => $url,
            'file_size' => $currentfile_size,
            'folder_id' => $request->folder_id,
        ]);

        $user->files()->create($file->toArray());;

        $folder=Folder::find($request->folder_id);
        
        // // Update the used storage of the user
        $folder->folder_size +=$currentfile_size;
        $user->used_storage += $currentfile_size ;
        $user->number_of_files +=1;

        $user->save();
        $folder->save();

        return response([
            'message' => 'File uploaded successfully.',
            'file' => $file,
        ], 201);
    }
 
    /**
     * Display the files of folder.
     */
    public function showfiles($id)
    {
        $files= DB::table('files')
                ->whereIn('folder_id', [$id])
                ->orderBy('created_at', 'desc')
                ->get();

        return $files;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $file = File::find($id);

        if ($file && $file->folder->user_id == auth()->id()) {
            $url = str_replace(asset('storage/'), "public", $file->file_link);
            Storage::delete($url);
            
            $user = auth()->user();
            $folder= $file->folder;
            $user->number_of_files -=1;
            $user->used_storage -= $file->file_size;
            $folder->folder_size -=$file->file_size;
            $user->save();
            $folder->save();

            $file->delete();
            return response()->json(['message' => 'File deleted successfully'], 200);
        } else {
            return response()->json(['error' => 'Forbidden'], 403);
        }
    }
}
