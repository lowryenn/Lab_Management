<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function apiCall($method, $path, $data = [])
    {
        $apiUrl = env('API_URL', 'http://127.0.0.1:3001');
        $url = rtrim($apiUrl, '/') . '/' . ltrim($path, '/');
        
        $request = Http::acceptJson();
        
        if (Auth::check()) {
            $user = Auth::user();
            $token = $this->generateJwt($user);
            $request = $request->withToken($token);
        }
        
        $method = strtolower($method);
        if ($method === 'get') {
            $response = $request->get($url, $data);
        } elseif ($method === 'post') {
            $response = $request->post($url, $data);
        } elseif ($method === 'put') {
            $response = $request->put($url, $data);
        } elseif ($method === 'delete') {
            $response = $request->delete($url, $data);
        } elseif ($method === 'patch') {
            $response = $request->patch($url, $data);
        } else {
            $response = $request->send($method, $url, ['json' => $data]);
        }
        
        return $response;
    }
    
    private function generateJwt($user)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'name' => $user->name,
            'iat' => time(),
            'exp' => time() + 86400 // 24 hours
        ]);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        $secret = env('JWT_SECRET', 'lab-management-secret-key-change-me-in-production');
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    protected function hydrateModel($class, $data)
    {
        if (empty($data)) return null;
        $model = new $class();
        $model->exists = true;
        $model->setRawAttributes((array) $data, true);
        return $model;
    }

    protected function hydrateCollection($class, $data)
    {
        if (empty($data)) return collect();
        return $class::hydrate((array) $data);
    }
}
