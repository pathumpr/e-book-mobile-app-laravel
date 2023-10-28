<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Writer;
use App\Models\Reader;
use App\Models\Books;
use App\Models\Category;

class WriterrController extends Controller
{
    public function register(Request $req){

        //Email validation
        $email_exists = Writer::where('email', $req->email)->first();
        $email_exists2 = Reader::where('email', $req->email)->first();
        if($email_exists != null){
            return response()->json(['msg' => 'email_exists',]);
        }else if($email_exists2 != null){
            return response()->json(['msg' => 'email_exists',]);
        }
        
        //Phone number validation
        $phone_exists = Writer::where('phone_number', $req->phoneNumber)->first();
        $phone_exists2 = Reader::where('phone_number', $req->phoneNumber)->first();
        if($phone_exists != null){
            return response()->json(['msg' => 'phone_exists',]);
        }else if($phone_exists2 != null){
            return response()->json(['msg' => 'phone_exists',]);
        }

        //hash password
        $hashedPassword = bcrypt($req->password);

        $reader = new Writer;
        $reader->username = $req->username;
        $reader->email = $req->email;
        $reader->phone_number = $req->phoneNumber;
        $reader->status = $req->status;
        // $reader->profile_pic = $req->profilePhoto;
        $reader->password = $hashedPassword;
        $reader->save();

        $user = Writer::where('email', $req->email)->first();
        
        return response()->json([
            'msg' => 'success',
            'id' => $user->id,
        ]);

    }

    public function upload_profile_pic_writer(Request $request)
    {
        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationPath = 'profile_pics'; // Folder path inside the 'storage/app/public' directory
    
        // Create the directory if it doesn't exist
        Storage::disk('public')->makeDirectory($destinationPath);
    
        $file->storeAs($destinationPath, $fileName, 'public'); // Store the file in the 'public' disk
    
        // Save the file path to the database
        $photoPath = 'storage/' . $destinationPath . '/' . $fileName; // The path to the stored file
                
        $id = $request->input('id');
        $reader = Writer::find($id);
        $reader->profile_pic = $photoPath;
        $reader->save();
    
        return response()->json([
            'msg' => 'success',
        ]);
    }

    public function login(Request $request){
        $user = Writer::where('email', $request->email)->where('status', 1)->first();

        // Check if the user exists and status is 1
        if ($user && $user->status == 1) {
            // Verify the password
            if (Hash::check($request->password, $user->password)) {

                // Password matches, user is authenticated
                $profile_pic = '';

                // Check if the user has a profile picture
                if ($user->profile_pic) {
                    // Get the profile picture URL from the storage
                    $profilePicUrl = Storage::url($user->profile_pic);

                    // Add the profilePic URL to the response
                    $modifiedData = str_replace('/storage', '', $profilePicUrl);
                    $profile_pic = '/storage/' . ltrim($modifiedData, '/');

                } else {
                    // If the user doesn't have a profile picture, set it to null
                    $profile_pic = 'n';
                }

                return response()->json([
                    'msg' => 'success',
                    'username' => $user->username,
                    'id' => $user->id,
                    'profilePic' => $profile_pic,
                ]);

            } else {
                return response()->json(['msg' => 'invalid']);
            }
        } else {
            return response()->json(['msg' => 'not_found']);
        }
    }

    public function add_book(Request $request){

        $minDigits = 99999;
        $maxDigits = 999999999;
        $numDigits = random_int($minDigits, $maxDigits);

        $reader = new Books;
        $reader->book_id = $numDigits;
        $reader->name = $request->bookName;
        $reader->writer = $request->globalUsername;
        $reader->writer_id = $request->globalId;
        $reader->description = $request->description;
        $reader->category = $request->category;
        $reader->price = $request->price;
        $reader->save();

        $user = Books::where('writer', $request->email)->first();
       
        return response()->json([
            'msg' => 'success',
            'bookId' => $numDigits,
        ]);
    }

    public function add_book_image(Request $request){
        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationPath = 'books'; // Folder path inside the 'storage/app/public' directory
    
        // Create the directory if it doesn't exist
        Storage::disk('public')->makeDirectory($destinationPath);
    
        $file->storeAs($destinationPath, $fileName, 'public'); // Store the file in the 'public' disk
    
        // Save the file path to the database
        $photoPath = 'storage/' . $destinationPath . '/' . $fileName; // The path to the stored file
                
        $id = $request->input('id');
        $reader = Books::where('book_id', intval($id))->first();
        $reader->photo = $photoPath;
        $reader->save();
    
        return response()->json([
            'msg' => 'success',
        ]);
    }

    public function add_book_file(Request $request){
        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationPath = 'files'; // Folder path inside the 'storage/app/public' directory
    
        // Create the directory if it doesn't exist
        Storage::disk('public')->makeDirectory($destinationPath);
    
        $file->storeAs($destinationPath, $fileName, 'public'); // Store the file in the 'public' disk
    
        // Save the file path to the database
        $filePath = 'storage/' . $destinationPath . '/' . $fileName; // The path to the stored file
                
        $id = $request->input('id');
        $reader = Books::where('book_id', intval($id))->first();
        $reader->file = $filePath;
        $reader->save();
    
        return response()->json([
            'msg' => 'success',
        ]);
    }

    public function get_writer_books($id){

        $books = Books::where('writer_id', $id)->where('status', 1)->get();

        $count = $books->count();

        return response()->json([
            'data' => $books,
            'count' => $count
        ]); 

    }

    public function delete_book_writer($id){

        $book = Books::where('book_id', $id)->first();
        $book->status = 0;
        $book->save();

        return response()->json([
            'msg' => 'success'
        ]); 
    }

    public function get_edit_book_data($id){

        $book = Books::where('book_id', $id)->first();

        return response()->json([
            'msg' => 'success',
            'data' => $book
        ]); 
    }

    public function update_book_data(Request $request){

        $book = Books::where('book_id', $request->bookId)->first();
        $book->name = $request->bookName;
        $book->description = $request->description;
        $book->category = $request->category;
        $book->save();

        return response()->json([
            'msg' => 'success',
        ]); 

    }

    public function update_book_image(Request $request){
        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationPath = 'books'; // Folder path inside the 'storage/app/public' directory
    
        // Create the directory if it doesn't exist
        Storage::disk('public')->makeDirectory($destinationPath);
    
        $file->storeAs($destinationPath, $fileName, 'public'); // Store the file in the 'public' disk
    
        // Save the file path to the database
        $photoPath = 'storage/' . $destinationPath . '/' . $fileName; // The path to the stored file
                
        $id = $request->input('id');
        $reader = Books::where('book_id', intval($id))->first();

        $path = str_replace('storage/', '', $reader->photo);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            $reader->photo = $photoPath;
            $reader->save();
            return response()->json([
                'msg' => 'success',
                'log' => 'old photo deleted'
            ]);
        }else{
            $reader->photo = $photoPath;
            $reader->save();
            return response()->json([
                'msg' => 'success',
                'log' => $reader->photo
            ]);
        }

    }

    public function delete_writer($id){

        $user = Writer::where('id', $id)->where('status', 1)->first();
        $user->status = 0;
        $user->save();

        return response()->json([
            'msg' => 'success'
        ]);
        
    }

    public function get_categories(){
        $Categories = Category::where('status', 1)->get();

        $count = $Categories->count();

        return response()->json([
            'data' => $Categories,
            'count' => $count
        ]); 
    }


    // public function test(){
    //     return response()->json([
    //         'msg' => 'success',
    //     ]);
    // }
}
