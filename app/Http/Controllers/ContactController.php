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

class ContactController extends Controller
{
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
            $contacts = $section->contacts()->where('status', 'accepted')->get();
            array_push($data, [
                $section->name => $contacts
            ]);
        }

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

        // sending email for normal user.
        if (auth('api')->user()->role_id == 3) {
            $admin = User::where('role_id', '<', 3)->limit(1)->get()->first();
            // swap 'omaragha9595@gmail.com' with $admin->email;
            Mail::to('omaragha9595@gmail.com')->send(new MyMail('Query Add Contact', auth('api')->user()->email, auth('api')->user()->name, $request->first_name, $request->last_name, $request->mobile, $request->address));
            Order::create(['contact_id' => $contact->id]);
        }


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


    public function getAllOrders() {
        if (!auth('api')->user() || auth('api')->user()->role_id == 3) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $orders = Order::all();
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
        return response()->json([
            'message' => 'success',
            'orders'  => $data
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
        if (request('status') == 'rejected') {
            $contact->delete();            
        } else {
            $contact->status = request('status');
            $contact->save();
        }
        return response()->json([
            'message' => 'Status changed successfully.'
        ], 200);
    }

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
            }
        }

        foreach($ids as $id) {
            $contact = Contact::find($id);
            $contact->order()->delete();
            $contact->delete();
        }

        Contact::create($joinedContact + ['user_id' => auth('api')->user()->id, 'section_id' => auth('api')->user()->section_id]);


        return response()->json([
            'message' => 'Mergin contacts successfully.'
        ], 200);

    }


}
