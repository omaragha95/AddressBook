<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\DB;

use App\Contact;
use App\Locked;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Client;

class ContactController extends Controller
{
    public static $locked = 0;
    public function index() {
        if (!auth('api')->use() || auth('api')->user()->role_id == 3) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $contacts = Contact::all();
        return response()->json([
            'contacts' => $contacts
        ], 200);
    }

    public function indexBySection($id) {
        if (!auth('api')->user() || auth('api')->user()->role_id == 3) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $contacts = Contact::where('section_id', $id)->get();
        return response()->json([
            'message' => 'success',
            'contacts' => $contacts
        ], 200);
    }

    public function myContacts() {
        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $contacts = Contact::where('user_id', auth('api')->user()->id)->get();
        return response()->json([
            'message' => 'success',
            'contacts' => $contacts
        ], 200);
    }



    public function create(StoreContactRequest $request) {
        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // sending email for normal user.
        if (auth('api')->user()->role_id == 3) {

        }

        $contact = Contact::create($request->all() + ['user_id' => auth('api')->user()->id, 'section_id' => auth('api')->user()->section_id]);

        return response()->json([
            'message' => 'Contact created successfully.',
            'id'      => $contact->id
        ], 200);
    }

    public function update($id) {

        if (ContactController::$locked == 1) {
            return response()->json([
                'message' => 'This contact is locked by another transaction.'
            ], 422);
        }

        $contact = Contact::find($id);

        if (!$id) {
            return response()->json([
                'message' => 'Contact NOT found.'
            ], 404);
        }

        if (!auth('api')->user() || (auth('api')->user()->role_id != 1 && auth('api')->user()->id != 
        $contact->user_id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $contact = Contact::find($id);

        $locked = Locked::find($contact->id);

        if ($locked) {
            return response()->json([
                'message' => 'this record is locked by another transaction.'
            ], 422);
        }

        Locked::create([
            'record_id' => $contact->id
        ]);

        $message = '';
        $status = 200;
        sleep(10);

        // DB::beginTransaction();

        // try {
        //     $contact->first_name = request('first_name');
        //     $contact->last_name = request('last_name');
        //     $contact->mobile = request('mobile');
        //     $contact->address = request('address');
        //     $contact->save();
        //     $message = 'Contact updated successfully.';
        //     $status = 200;
        //     sleep(20);
        //     DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     $status = 500;
        //     $message = 'Internal error.';
        // }
        // Locked::where('record_id', $contact->id)->delete();
        ContactController::$locked = 0;
        return response()->json([
            'message' => $message
        ], $status);
    }


    public function delete($id) {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json([
                'message' => 'Contact NOT found!'
            ], 422);
        }
        if (!auth('api')->user() || (auth('api')->user()->role_id != 1 && auth('api')->user()->id != $contact->user_id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $contact->delete();
        return response()->json([
            'message' => 'Contact deleted successfully.'
        ], 200);
    }

    public function test() {
        return 'omar';
    }


}
