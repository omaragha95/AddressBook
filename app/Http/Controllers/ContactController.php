<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\DB;


use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Client;

use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;

use App\Contact;
use App\Order;
use App\User;
use App\Section;
use App\Log;

class ContactController extends Controller
{

    public static $process = [];

    public function __construct() {
        $this->middleware('transaction', ['only' => [
            'update' // Could add bunch of more methods too
        ]]);
    }

    public function index() {
        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $sections;
        $data = [];

        if (auth('api')->user()->role_id == 3) {
            $sections = [Section::find(auth('api')->user()->section_id)];
        } else {
            $sections = Section::all();
        }

        foreach($sections as $section) {
            $contacts = $section->contacts()->where('status', 'pending')->get();
            array_push($data, [
                'section' => $section->name, 'contacts' => $contacts
            ]);
        }

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'get all sections and thier contacts.',
            'table'  => 'contacts and section',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message'  => 'success',
            'data' => $data
        ], 200);
    }


    public function create(StoreContactRequest $request) {
        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $contact = Contact::create($request->all() + ['user_id' => auth('api')->user()->id, 'section_id' => auth('api')->user()->section_id]);

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'Add new Contact.',
            'table'  => 'contacts',
            'role'   => auth('api')->user()->role->name            
        ]);

        // sending email for normal user.
        if (auth('api')->user()->role_id == 3) {
            $admin = User::where('role_id', '<', 3)->limit(1)->get()->first();
            // swap 'omaragha9595@gmail.com' with $admin->email;
            Mail::to('omaragha9595@gmail.com')->send(new MyMail('Query Add Contact', auth('api')->user()->email, auth('api')->user()->name, $request->first_name, $request->last_name, $request->mobile, $request->address));
            Order::create(['contact_id' => $contact->id, 'user_id' => $admin->id]);
            Log::create([
                'name' => auth('api')->user()->name,
                'email' => auth('api')->user()->email,
                'action' => 'Add new order.',
                'table'  => 'orders',
                'role'   => auth('api')->user()->role->name            
            ]);    
        }
        return response()->json([
            'message' => 'Contact created successfully.',
            'id'      => $contact->id
        ], 200);
    }

    public function update($id) {

        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $contact = Contact::find($id);

        if (auth('api')->user()->role_id == 3 && auth('api')->user()->id != $contact->user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if (in_array($id, ContactController::$process)) {
            return response()->json([
                'message' => 'You can not update this contact because updating by another user.'
            ], 422);
        }
        
        array_push(ContactController::$process, $id);

        DB::beginTransaction();

        try {
            $contact = Contact::find($id);
            $contact->first_name = request('first_name');
            $contact->last_name = request('last_name');
            $contact->mobile = request('mobile');
            $contact->address = request('address');
            $contact->save();
            DB::commit();
        } catch (\Excerption $e) {
            DB::rollback();
        }

        $index = array_search($id, $process);
        if ($index) {
            unset($process[$index]);
            array_values($process);
        }

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'updating contact.',
            'table'  => 'contacts',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message' => 'updated contact successfully.'
        ], 200);
    }


    public function delete($id) {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json([
                'message' => 'Contact NOT found!'
            ], 404);
        }

        if (!auth('api')->user() || (auth('api')->user()->role_id != 1 && auth('api')->user()->id != $contact->user_id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $contact->order()->delete();

        $contact->delete();

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'deleting contact.',
            'table'  => 'contacts',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message' => 'Contact deleted successfully.'
        ], 200);
    }


    public function getAllOrders() {
        if (!auth('api')->user() || auth('api')->user()->role_id == 3) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // $orders = Order::all();
        $orders = auth('api')->user()->orders;
        $data = [];

        foreach($orders as $order) {
            $contact = Contact::find($order->contact_id);
            $user = User::find($contact->user_id);
            $ordr = [
                'id'         => $order->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'first_name' => $contact->first_name,
                'last_name'  => $contact->last_name,
                'mobile'     => $contact->mobile,
                'address'    => $contact->address,
                'status'     => $contact->status
            ];
            array_push($data, $ordr);
        }
        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'getting all orders.',
            'table'  => 'orders',
            'role'   => auth('api')->user()->role->name
        ]);
        return response()->json([
            'message' => 'success',
            'data'  => $data
        ], 200);
    }

    public function changeStatus($id) {
        if (!auth('api')->user() || auth('api')->user()->role_id == 3) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        Order::where('contact_id', $id)->delete();

        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'message' => 'Contact NOT found!'
            ], 404);
        }

        if (request('status') == 'rejected') {
            $contact->delete();            
        } else {
            $contact->status = request('status');
            $contact->save();
        }
        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'changing status contact.',
            'table'  => 'contacts',
            'role'   => auth('api')->user()->role->name
        ]);
        return response()->json([
            'message' => 'Status changed successfully.'
        ], 200);
    }


    // lock for any updating operation.
    public function join(Request $request) {

        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $ids = $request->ids;
        $joinedContact = $request->contact;

        if (auth('api')->user()->role_id != 1) {
            foreach($ids as $id) {
                $contact = auth('api')->user()->contacts()
                           ->where('id', $id)->where('user_id', auth('api')->user()->id)
                           ->get()->first();
                if (!$contact) {
                    return response()->json([
                        'message' => 'Unauthorized'
                    ], 401);
                }
                if (in_array($id, ContactCotnroller::$process)) {
                    return response()->json([
                        'message' => 'You can not update this contact, because it is updating by another user.'
                    ], 422);
                }
            }
        }

        foreach($ids as $id) {
            array_push(ContactController::$process, $id);
        }

        DB::beginTransaction();
        
        try {
            foreach($ids as $id) {
                $contact = Contact::find($id);
                $contact->order()->delete();
                $contact->delete();
            }
    
            Contact::create($joinedContact + ['user_id' => auth('api')->user()->id, 'section_id' => auth('api')->user()->section_id]);
            
            unset(ContactController::$process);
            ContactController::$process = [];

            DB::commit();

            Log::create([
                'name' => auth('api')->user()->name,
                'email' => auth('api')->user()->email,
                'action' => 'joining muliple contacts.',
                'table'  => 'contacts',
                'role'   => auth('api')->user()->role->name
            ]);
    
    
            return response()->json([
                'message' => 'Mergin contacts successfully.'
            ], 200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Internal error.'
            ], 500);
        }

    }

}
