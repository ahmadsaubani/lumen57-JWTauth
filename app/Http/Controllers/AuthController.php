<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL()
        ]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        try {

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Bad Credentials'], 401);
        }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['Token Expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['Invalid Token'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['Absent Token' => $e->getMessage()], 500);

        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $user =  User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        return response()->json($user, 201);
    }

    public function getUserById($id)
    {
        $user = User::find($id);
        if($user === null) {
            return response()->json([
                'message'=> 'User Not Found'], 404);
            }
        return response()->json($user, 200);
    }

    public function getAllUser()
    {
        $user = User::all();

        return  response()->json($user, 200);
    }

    public function me()
    {
        return response()->json(Auth::user(), 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

            if(!$user){
                return response()->json(['message' => "Failed Update Password"], 404);
            }
                $this->validate($request, [
                $user->email        = $user->email,
                $user->password     = Hash::make($request->get('password'))
                ]);

        $user->save();

        return response()->json(['msg' =>  'Change Password Success'],200);
    }

    public function delete($id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json(['msg' => "User Not Found"], 404);
        }

        $user->delete();

        return response()->json(['msg' => "Delete Success"], 200);
    }

    public function logout()
    {
        $user = Auth::logout(true);

        return response()->json(['msg' => 'success logout'], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $user = $request->all();

        return $this->respondWithToken(Auth::refresh($user));
    }
}