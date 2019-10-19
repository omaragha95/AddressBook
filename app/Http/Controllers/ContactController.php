<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\DB;

use App\Contact;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Client;

class ContactController extends Controller
{
    public function index() {
        if (!auth('api')->use() || auth('api')->user()->role_id == 3) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $contacts = Contact::all();
        return response()->json([
            'message'  => 'success',
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


}
