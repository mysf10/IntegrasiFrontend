<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Firebase\JWT\JWT;

class AuthController extends Controller 
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(Request $request) //Abiyyu Dwi Fawwazy
  {
    //
    $this->request = $request;
  }
  //
  protected function jwt(Mahasiswa $user) //Abiyyu Dwi Fawwazy
  {
    $payload = [
      'iss' => 'lumen-jwt', //issuer of the token
      'sub' => $user->nim, //subject of the token
      'iat' => time(), //time when JWT was issued.
      'exp' => time() + 60 * 60 //time when JWT will expire
    ];
    return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
  }


  public function register(Request $request)//Abiyyu Dwi Fawwazy
  {
    $nim = $request->nim;
    $nama = $request->nama;
    $angkatan = $request->angkatan;
    $password = Hash::make($request->password);

    $user = Mahasiswa::create([
      'nim' => $nim,
      'nama' => $nama,
      'angkatan' => $angkatan,
      'password' => $password
    ]);

    return response()->json([
      'status' => 'Success',
      'message' => 'new user created',
      'user' => $user,
    ], 200);
  }

  public function login(Request $request) //Abiyyu Dwi Fawwazy
  {
    $nim = $request->nim;
    $password = $request->password;
    $user = Mahasiswa::where('nim', $nim)->first();
    if (!$user) {
      return response()->json([
        'status' => 'Error',
        'message' => 'user not exist',
      ], 404);
    }
    if (!Hash::check($password, $user->password)) {
      return response()->json([
        'status' => 'Error',
        'message' => 'wrong password',
      ], 400);
    }
    $user->token = $this->jwt($user); //
    $user->save();
    return response()->json([
      'status' => 'Success',
      'message' => 'successfully login',
      'token' => $user->token,
    ], 200);
  }

  private function base64url_encode(String $data): String //Abiyyu Dwi Fawwazy
  {
    $base64 = base64_encode($data); // ubah json string menjadi base64
    $base64url = strtr($base64, '+/', '-_'); // ubah char '+' -> '-' dan '/' -> '_'
    return rtrim($base64url, '='); // menghilangkan '=' pada akhir string
  }
  private function sign(String $header, String $payload, String $secret): String //Abiyyu Dwi Fawwazy
  {
    $signature = hash_hmac('sha256', "{$header}.{$payload}", $secret, true);
    $signature_base64url = $this->base64url_encode($signature);
    return $signature_base64url;
  }
  // private function jwt(array $header, array $payload, String $secret): String
  // {
  //   $header_json = json_encode($header);
  //   $payload_json = json_encode($payload);
  //   $header_base64url = $this->base64url_encode($header_json);
  //   $payload_base64url = $this->base64url_encode($payload_json);
  //   $signature_base64url = $this->sign($header_base64url, $payload_base64url, $secret);
  //   $jwt = "{$header_base64url}.{$payload_base64url}.{$signature_base64url}";
  //   return $jwt;
  // }
}
