<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Reader;
use App\Models\Writer;
use App\Models\Books;
use App\Models\Payment;
use App\Models\Category;

class ReaderController extends Controller
{
    public function register(Request $req){

        //Email validation
        $email_exists = Reader::where('email', $req->email)->first();
        $email_exists2 = Writer::where('email', $req->email)->first();
        if($email_exists != null){
            return response()->json(['msg' => 'email_exists',]);
        }else if($email_exists2 != null){
            return response()->json(['msg' => 'email_exists',]);
        }
        
        //Phone number validation
        $phone_exists = Reader::where('phone_number', $req->phoneNumber)->first();
        $phone_exists2 = Writer::where('phone_number', $req->phoneNumber)->first();
        if($phone_exists != null){
            return response()->json(['msg' => 'phone_exists',]);
        }else if($phone_exists2 != null){
            return response()->json(['msg' => 'phone_exists',]);
        }

        //hash password
        $hashedPassword = bcrypt($req->password);

        $reader = new Reader;
        $reader->username = $req->username;
        $reader->email = $req->email;
        $reader->phone_number = $req->phoneNumber;
        $reader->status = $req->status;
        // $reader->profile_pic = $req->profilePic;
        $reader->password = $hashedPassword;
        $reader->save();

        $user = Reader::where('email', $req->email)->first();

        return response()->json([
            'msg' => 'success',
            'id' => $user->id,
        ]);

    }

    public function upload_profile_pic_reader(Request $request)
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
        $reader = Reader::find($id);
        $reader->profile_pic = $photoPath;
        $reader->save();
    
        return response()->json([
            'msg' => 'success',
        ]);
    }

    public function login(Request $request){
        $user = Reader::where('email', $request->email)->where('status', 1)->first();

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

    public function get_all_books(){

        $books = Books::where('status', 1)->get();
        $Categories = Category::where('status', 1)->get();

        $count = $books->count();
        $count_c = $Categories->count();

        return response()->json([
            'data' => $books,
            'count' => $count,
            'data_c' => $Categories,
            'count_c' => $count_c,
        ]);

    }

    public function get_single_book($id){

        $book = Books::where('book_id', $id)->first();

        return response()->json([
            'msg' => 'success',
            'data' => $book
        ]); 

    }

    public function checkout(Request $request){

        $data = new Payment;
        $data->book_id = $request->bookId;
        $data->price = $request->price;
        $data->writer_id = $request->writerId;
        $data->reader_id = $request->globalId;
        $data->payment_method = $request->method;
        $data->save();

        return response()->json([
            'msg' => 'success',
        ]); 

    }

    public function get_purchased_books($id){

        $books = Payment::where('reader_id', $id)->where('status', 1)->get();

        $count = $books->count();

        //filter only book id
        $decodedData = json_decode($books, true);
        $bookIds = [];
        foreach ($decodedData as $item) {
            if (isset($item['book_id'])) {
                $bookIds[$item['id']] = $item['book_id'];
            }
        }

        $values = array_values($bookIds);
        $books = Books::whereIn('book_id', $values)->get();
        $booksArray = $books->toArray();

        return response()->json([
            'data' => $booksArray,
            'count' => $count
        ]); 

    }

    public function delete_purchased_book($id){

        $books = Payment::where('book_id', $id)->get();
        // $book->status = 0;
        // $book->save();
        foreach ($books as $book) {
            $book->status = 0;
            $book->save();
        }

        return response()->json([
            'msg' => 'success'
        ]); 
    }

    public function delete_reader($id){

        $user = Reader::where('id', $id)->where('status', 1)->first();
        $user->status = 0;
        $user->save();

        return response()->json([
            'msg' => 'success'
        ]);
        
    }

    public function get_categories_books($id){
        $books = Books::where('status', 1)->where('category', $id)->get();

        $count = $books->count();

        return response()->json([
            'data' => $books,
            'count' => $count
        ]);
    }

    public function search_books(Request $request){

        $searchText = $request->text; // Assuming the input text is sent via a 'search' parameter in the request.
        $books = Books::where('name', 'LIKE', "%{$searchText}%")->get();
        $count = $books->count();
    
        return response()->json([
            'data' => $books,
            'count' => $count
        ]);
        
    }

}
