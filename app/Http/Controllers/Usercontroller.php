<?php

namespace App\Http\Controllers;

use App\Models\Cartproduct;
use App\Models\Helprequest;
use App\Models\Order;
use App\Models\Productsdetail;
use Illuminate\Support\Facades\Log;
use App\Models\Registration;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    private $firstname;
    private $lastname;
    private $mobile;
    private $password;
    private $accountcreateat;
    private $accountupdatedat;

    public function registration(Request $request)
    {
        try {
            $messages = [
                'unique'    => 'The User is already registerd',
            ];
            // Validate the request data
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:20',
                'lastname' => 'required|string|max:20',
                'mobile' => 'required|numeric|digits:10|unique:registrations,mobile',
                'password' => 'required|string|min:6',

            ], $messages);

            // Save user data
            $obj = new Registration();
            $obj->firstname = $validatedData['firstname'];
            $obj->lastname = $validatedData['lastname'];
            $obj->mobile = $validatedData['mobile'];

            $orderId = uniqid('USER_');
            $obj->userid = $orderId;
            $obj->password = Hash::make($validatedData['password']);

            if ($obj->save()) {
                return response()->json(['success' => 'Your registration was successfully completed'], 201);
            } else {
                return response()->json(['error' => 'An error occurred during registration. Please try again.'], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors with a 422 status
            return response()->json(['errors' => $e->errors()], 422);
        }
    }




    public function login(Request $request)
    {

        $validData = $request->validate([
            'mobile' => 'required|digits:10',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($validData)) {
            return response()->json([
                'userdatils' => Auth::user(),
                'success' => 'You have successfully logged in',
            ], 200);
        } else {

            return response()->json([
                'unsuccess' => 'Invalid mobile number or password',
            ], 401);
        }
    }


    public function fetchproducts()
    {
        $obj = new Productsdetail();
        $data = $obj->get();

        if ($data) {
            return response()->json($data);
        } else {
            return response()->json(['nofound' => "produts data is anvailble"]);
        }
    }


    public function addcart(Request $request)
    {

        $productids = $request->all();


        foreach ($productids as $key => $value) {
            $productobj = new Cartproduct();
            // Log::info($value); // Log received data means debugin the deta is recived is corretlly or not
            $productobj->productid = $value;
            $productobj->addcartproductid = mt_rand();
            
            $productobj->save();
        }

        return response()->json([
            'message' => 'Data received successfully',
        ]);
    }
    public function getcartitems()
    {

        $databasecart_p = Cartproduct::all();

        if ($databasecart_p) {
            return response()->json($databasecart_p);
        } else {
            return response()->json([
                'error' => "cart item is not found",
            ]);
        }
    }



    public function removeitemsfromcart(Request $request)
    {

        $array = json_decode($request->getContent());

        $count = 0;
        foreach ($array as $key => $value) {

            $deleted = Cartproduct::where('productid', $value)->delete();

            if ($deleted) {
                $count++;
            }
        }

        if ($count > 0) {
            return response()->json(['success' => "row deleted succefully"], 201);
        } else {
            return response()->json(['success' => "row deleted unsuccefully"], 401);
        }
    }





    public function order_place(Request $request)
    {





        $validatedData = $request->validate([
            'user.name'     => 'required|string|max:255',
            'user.address'  => 'required|string|max:500',
            'user.mobile'   => 'required|string|regex:/^[0-9]{10}$/',
            'user.pincode'  => 'required|string|regex:/^[0-9]{4,8}$/',
            'user.city'     => 'required|string|max:255',
            'user.states'   => 'required|string|max:255',
            'products'      => 'required|array|min:1',
            'products.*.name'      => 'required|string|max:255',
            'products.*.productid' => 'required|string|max:50',
            'products.*.imageurl' => 'required',
            'products.*.price'     => 'required|numeric|min:0',
            'products.*.quantity'  => 'required|integer|min:1',
            'products.*.total'     => 'required|numeric|min:0',
        ]);

        $orders = [];

        // Check if only one product exists
        if (count($validatedData['products']) === 1) {
            $product = $validatedData['products'][0]; // Get the single product
            $orderId = uniqid('ORD_');
            $orders[] = [
                'order_id'      => $orderId,
                'customer_name' => $validatedData['user']['name'],
                'address'       => $validatedData['user']['address'],
                'mobile'        => $validatedData['user']['mobile'],
                'pincode'       => $validatedData['user']['pincode'],
                'city'          => $validatedData['user']['city'],
                'state'         => $validatedData['user']['states'],
                'product_name'  => $product['name'],
                'p_id'          => $product['productid'],
                'payment_mode'          =>  $request->paymentMethod,
                'imageurl' =>$product['imageurl'],
                'p_price'       => $product['price'],
                'p_qut'         => $product['quantity'],
                'p_total'       => $product['total'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
            $obj = Productsdetail::where('productid', $product['productid'])->first();

            if ($obj->Quantity >= $product['quantity']) {
                $availble = $obj->Quantity - $product['quantity'];
                $obj->Quantity = $availble;
                $res = $obj->save();

                if (!$res) {
                    return response()->json([
                        'unsuccess' => 'Product Quantity is UnAvailble',
                    ], 500);
                }
            }
        } else {
            foreach ($validatedData['products'] as $product) {
                $orderId = uniqid('ORD_');
                $orders[] = [
                    'order_id'      => $orderId,
                    'customer_name' => $validatedData['user']['name'],
                    'address'       => $validatedData['user']['address'],
                    'mobile'        => $validatedData['user']['mobile'],
                    'pincode'       => $validatedData['user']['pincode'],
                    'city'          => $validatedData['user']['city'],
                    'state'         => $validatedData['user']['states'],
                    'product_name'  => $product['name'],
                    'payment_mode'          =>  $request->paymentMethod,
                    'p_id'          => $product['productid'],
                    'p_price'       => $product['price'],
                    'p_qut'         => $product['quantity'],
                    'p_total'       => $product['total'],
                    'imageurl'      =>$product['imageurl'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
                $obj = Productsdetail::where('productid', $product['productid'])->first();

                if ($obj->Quantity >= $product['quantity']) {
                    $availble = $obj->Quantity - $product['quantity'];
                    $obj->Quantity = $availble;
                    $res = $obj->save();

                    if (!$res) {
                        return response()->json([
                            'unsuccess' => 'Product Quantity is UnAvailble',
                        ], 500);
                    }
                }
            }
        }

        $result = Order::insert($orders);


        if ($result) {
            return response()->json([
                'success' => 'Order placed successfully',
            ], 200);
        } else {
            return response()->json([
                'unsuccess' => 'Order Not placed successfully',
            ], 500);
        }
    }


    public function ordersdata(){
        $obj = new Order();
        $data = $obj->get();

        if ($data) {
            return response()->json($data);
        } else {
            return response()->json(['nofound' => "order data is unvailble"]);
        }
    }


    public function cancelorder(Request $request){
    $obj = Order::where('order_id',  $request->orderid)->first();

        $obj->status='0';
       
        $res = $obj->save();

        if ($res) {
            return response()->json([
                'success' => 'Product cancel Successfully',
            ], 200);
        }else{
            return response()->json([
                'unsuccess' => 'Product cancel unSuccessfully',
            ], 500);

        }
    }




        public function help_request(Request $request)
        {
            try {
                $validData = $request->validate([
                
                    'order_id' => 'required',
                    'concern' => 'required|string|max:150',
                ]);
        
                $obj = new Helprequest();
                $obj->order_id = $validData['order_id'];
                $obj->concern = $validData['concern'];
           
        
                if ($obj->save()) {
                    return response()->json([
                        'success' => "Your concern successfully sent"
                    ], 200);
                } else {
                    return response()->json([
                        'unsuccess' => "Something went wrong. Please try again."
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'unsuccess' => $e->getMessage()
                ], 500);
            }
        }
        

}
