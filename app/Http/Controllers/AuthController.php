<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
// use Smsirlaravel;
use SMSIR;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Events;
use App\Models\FactoryOptions;

use App\Models\OrderPremission;
use App\Models\CubePremission;
use App\Models\ClusterPremission;
use Throwable;

class AuthController extends Controller
{
    public function authFailed()
    {
        return response('unauthenticated', 401);
    }
    private function getResponse(User $user)
    {
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(2);
        $token->save();

        return response(
            [
                'accessToken' => $tokenResult->accessToken,
                'tokenType' => 'Bearer',
                'expiresAt' => Carbon::parse($token->expires_at)->toDateTimeString(),
            ],
            200,
        );
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 200);
        }

        $verification_code = rand(100001, 999999);

        $user = User::create([
            'name' => $request['name'],
            'mobile' => $request['mobile'],
            'password' => Hash::make($request['password']),
            'verification_code' => $verification_code,
        ]);
        if ($user) {
            try {
                $data2 = CubePremission::create([
                    'user_id' => $user['id'],
                    'cube_view' => true,
                    'cube_add' => true,
                    'cube_del' => true,
                    'cube_update' => true,
                    'cube_cutting' => true,
                ]);
                $data3 = ClusterPremission::create([
                    'user_id' => $user['id'],
                    'cluster_view' => true,
                    'cluster_add' => true,
                    'cluster_del' => true,
                    'cluster_update' => true,
                ]);
                $data4 = OrderPremission::create([
                    'user_id' => $user['id'],
                    'order_view' => true,
                    'order_add' => true,
                    'order_del' => true,
                    'order_update' => true,
                ]);
                // $this->sendSms($verification_code, $request->phone);
                return response('true', 200);
            } catch (Throwable $e) {
                return response('خطا در ارسال پین کد', 422);
            }
        } else {
            return response(['errors' => $user->errors()], 422);
        }
    }
    public function getVerify()
    {
        $mobile = \request()->mobile;
        $user = User::where('mobile', $mobile)->first();
        if ($user) {
            if ($user->status == false) {
                return response('حساب کاربری فعال نشده است', 200);
            } else {
                return response('حساب کاربری شما قبلا فعال شده است', 422);
            }
        } else {
            return response('خطایی به وجود آمده است - چنین کاربری در سیستم وجود ندارد', 422);
        }
    }
    public function hasUser()
    {
        $mobile = \request()->mobile;
        $user = User::where('mobile', $mobile)->first();
        if ($user) {
            return response(true);
        } else {
            return response(false);
        }
    }
    public function doVerify(Request $request)
    {
        $this->validate(
            $request,
            [
                'code' => 'required|integer',
            ],
            [
                'code.required' => 'فیلد کد ارسالی الزامی می باشد',
                'code.integer' => 'فرمت فیلد کد ارسالی نادرست می باشد',
            ],
        );

        $user = User::where('verification_code', $request->code)->first();
        if ($user) {
            $user->status = true;
            $user->verification_code = null;
            $user->save();
            return $this->getResponse($user);
        } else {
            return response('wrong code', 200);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/'],
            'password' => 'required|string|max:255',
            'remember_me' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $credentials = request(['mobile', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(2);
        }
        $token->save();
        if ($user->factoryId != null) {
            $this->loginEvent($user->name, $user->factoryId);
        }
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'factoryID' => $user->factoryId,
            'name' => $user->name,
            'profilepic' => $user->profilepic,
            'type' => $user->type,
            'mobile' => $user->mobile,
        ]);
    }
    public function logout(Request $request)
    {
        $this->newEvent('logout', auth('api')->user()->name . ' از پلاک سنگ خارج شد.', '', '');

        $request
            ->user()
            ->token()
            ->revoke();
        return true;
    }
    public function user(Request $request)
    {
        return $request->user();
    }
    public function getCurrentUser()
    {
        $data = new User();
        $data->name = auth('api')->user()->name;
        $data->mobile = auth('api')->user()->mobile;
        $data->profilepic = auth('api')->user()->profilepic;

        return $data;
    }
    public function sendResetPasswordPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:11|max:11|regex:/^09[0-9]{9}$/',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $phone = $request->input('phone');
        $user = User::where('phone', $phone)->first();
        if ($user) {
            if ($user->status == true) {
                $newPassword = rand(100001, 999999);
                $this->sendSms($newPassword, $request->phone);
                $user->password = $newPassword;
                $user->update();
            } else {
                return response('حساب شما فعال نمی باشد', 422);
            }
            return response('لینک فعال سازی به تلفن همراه ارسال شد', 200);
        } else {
            return response('چنین کاربری با شماره وارد شده یافت نشد', 422);
        }
    }
    public function sendSms(string $code, string $phone)
    {
        return;

        //Laravel 9
        // return Smsirlaravel::ultraFastSend(['Password' => $code], 22363, $phone);
        return SMSIR::ultraFastSend(['PassWord' => $code], 22363, $phone);

    }
    private function newEvent($type, $details, $itemtype, $sharingLinks)
    {
        $event = new EventsController();
        $event->newEvent($type, $details, $itemtype, $sharingLinks);
        return;
    }
    public function loginEvent($name, $factoryId)
    {
        // create login
        $event = new Events();
        $event->type = 'login';
        $event->user_id = 0;
        $event->factory_id = $factoryId;
        $event->sharingLinks = '';
        $event->item_type = '';
        $event->status = true;
        $event->details = $name . ' وارد پلاک سنگ شد.';
        $event->save();
        $eventCount = Events::where('factory_id', $factoryId)->count();
        $event = FactoryOptions::where('factory_id', $factoryId)->first();
        $savedEvent = $event->max_event_saved;

        if ($eventCount > $savedEvent) {
            $data = Events::where('factory_id', $factoryId)
                ->orderBy('id', 'asc')
                ->first();
            $data->delete();
        }
        return;
    }

    // /**
    // * Create user
    // *
    // * @param [string] name
    // * @param [string] email
    // * @param [string] password
    // * @param [string] password_confirmation
    // * @return [string] message
    // */
    // public function signup(Request $request)
    // {
    // $request->validate([
    // 'name' => 'required|string',
    // 'email' => 'required|string|email|unique:users',
    // 'password' => 'required|string',
    // 'profilepic' => 'required|string',

    // ]);
    // $user = new User([
    // 'name' => $request->name,
    // 'email' => $request->email,
    // 'password' => bcrypt($request->password),
    // 'profilepic' => $request->profilepic,
    // ]);
    // $user->save();
    // return response()->json([
    // 'message' => 'Successfully created user!'
    // ], 201);
    // }

    // /**
    // * Login user and create token
    // *
    // * @param [string] email
    // * @param [string] password
    // * @param [boolean] remember_me
    // * @return [string] access_token
    // * @return [string] token_type
    // * @return [string] expires_at
    // */
    // public function login(Request $request)
    // {
    // $request->validate([
    // 'email' => 'required|string|email',
    // 'password' => 'required|string',
    // 'remember_me' => 'boolean'
    // ]);
    // $credentials = request(['email', 'password']);
    // if(!Auth::attempt($credentials))
    // return response()->json([
    // 'message' => 'Unauthorized'
    // ], 401);
    // $user = $request->user();
    // $tokenResult = $user->createToken('Personal Access Token');
    // $token = $tokenResult->token;
    // if ($request->remember_me)
    // $token->expires_at = Carbon::now()->addWeeks(2);
    // $token->save();
    // return response()->json([
    // 'access_token' => $tokenResult->accessToken,
    // 'token_type' => 'Bearer',
    // 'expires_at' => Carbon::parse(
    // $tokenResult->token->expires_at
    // )->toDateTimeString()
    // ]);
    // }

    // /**
    // * Logout user (Revoke the token)
    // *
    // * @return [string] message
    // */
    // public function logout(Request $request)
    // {
    // $request->user()->token()->revoke();
    // return response()->json([
    // 'message' => 'Successfully logged out'
    // ]);
    // }

    // /**
    // * Get the authenticated User
    // *
    // * @return [json] user object
    // */
    // public function user(Request $request)
    // {
    // return response()->json($request->user());
    // }
}
