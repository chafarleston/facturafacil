<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\CoreFacturalo\Services\Dni\Dni;

class DecolectaController extends Controller
{
    public function search(Request $request)
    {
        $documento = $request->documento;
        $companyId = $request->company_id;
        
        $customer = Customer::where('company_id', $companyId)
            ->where('documento_numero', $documento)
            ->first();
        
        if ($customer) {
            return response()->json([
                'found' => true,
                'exists' => true,
                'customer' => [
                    'id' => $customer->id,
                    'nombre' => $customer->nombre,
                    'documento_tipo' => $customer->documento_tipo,
                    'documento_numero' => $customer->documento_numero,
                    'direccion' => $customer->direccion,
                    'email' => $customer->email,
                    'telefono' => $customer->telefono,
                ],
                'api_data' => [
                    'nombre' => $customer->nombre,
                    'direccion' => $customer->direccion ?? '',
                ]
            ]);
        }
        
        if (strlen($documento) === 11) {
            $sunatData = $this->searchInSunatPadron($documento);
            if ($sunatData) {
                return response()->json([
                    'found' => true,
                    'exists' => false,
                    'api_data' => [
                        'nombre' => $sunatData['razon_social'],
                        'direccion' => $sunatData['direccion'] ?? '',
                        'estado' => $sunatData['estado'] ?? '',
                        'condicion' => $sunatData['condicion'] ?? '',
                        'documento_tipo' => '6',
                        'documento_numero' => $documento,
                    ]
                ]);
            }
        }
        
        if (strlen($documento) === 8) {
            $dniData = $this->searchDni($documento);
            if ($dniData) {
                return response()->json([
                    'found' => true,
                    'exists' => false,
                    'api_data' => [
                        'nombre' => $dniData['nombre'],
                        'documento_tipo' => '1',
                        'documento_numero' => $documento,
                    ]
                ]);
            }
        }
        
        return response()->json([
            'found' => false,
            'exists' => false,
            'error' => 'Cliente no encontrado. Puede crear uno nuevo.',
            'api_data' => [
                'documento_tipo' => strlen($documento) === 11 ? '6' : '1',
                'documento_numero' => $documento,
                'nombre' => '',
                'direccion' => '',
            ]
        ]);
    }
    
    private function searchDni($dni)
    {
        $result = $this->searchEldni($dni);
        
        if ($result) {
            return [
                'dni' => $result['dni'],
                'nombre' => $result['nombre'],
                'apellido_paterno' => $result['apellido_paterno'],
                'apellido_materno' => $result['apellido_materno'],
                'nombres' => $result['nombres'],
            ];
        }
        
        try {
            $result = Dni::search($dni);
            
            if ($result && isset($result['success']) && $result['success']) {
                $person = $result['data'];
                return [
                    'dni' => $person->number,
                    'nombre' => $person->name,
                    'apellido_paterno' => $person->first_name ?? '',
                    'apellido_materno' => $person->last_name ?? '',
                    'nombres' => $person->names ?? '',
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error consultando DNI: ' . $e->getMessage());
        }
        
        return null;
    }
    
    private function searchEldni($dni)
    {
        $cookieFile = tempnam(sys_get_temp_dir(), 'eldni_');
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://eldni.com/pe/buscar-datos-por-dni');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            if (preg_match('/name="_token" value="([^"]*)"/', $response, $matches)) {
                $token = $matches[1];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://eldni.com/pe/buscar-datos-por-dni');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '_token=' . $token . '&dni=' . $dni);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_REFERER, 'https://eldni.com/pe/buscar-datos-por-dni');
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                $nombres = '';
                $apellidoPaterno = '';
                $apellidoMaterno = '';
                
                if (preg_match('/id="nombres" value="([^"]*)"/', $response, $matches)) {
                    $nombres = $matches[1];
                }
                if (preg_match('/id="apellidop" value="([^"]*)"/', $response, $matches)) {
                    $apellidoPaterno = $matches[1];
                }
                if (preg_match('/id="apellidom" value="([^"]*)"/', $response, $matches)) {
                    $apellidoMaterno = $matches[1];
                }
                
                if ($nombres || $apellidoPaterno || $apellidoMaterno) {
                    $nombreCompleto = trim($apellidoPaterno . ' ' . $apellidoMaterno . ' ' . $nombres);
                    
                    return [
                        'dni' => $dni,
                        'nombre' => $nombreCompleto,
                        'nombres' => $nombres,
                        'apellido_paterno' => $apellidoPaterno,
                        'apellido_materno' => $apellidoMaterno,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error en eldni.com: ' . $e->getMessage());
        } finally {
            if (file_exists($cookieFile)) {
                unlink($cookieFile);
            }
        }
        
        return null;
    }
    
    private function searchInSunatPadron($ruc)
    {
        // Primero buscar en archivo local
        $files = glob(storage_path('app/padron*.txt'));
        
        if (!empty($files)) {
            $filePath = $files[0];
            $handle = fopen($filePath, 'r');
            
            while (($line = fgets($handle)) !== false) {
                $parts = explode('|', trim($line));
                
                if (isset($parts[0]) && strlen($parts[0]) === 11 && $parts[0] === $ruc) {
                    fclose($handle);
                    return [
                        'ruc' => $parts[0] ?? '',
                        'razon_social' => $parts[1] ?? '',
                        'estado' => $parts[2] ?? '',
                        'condicion' => $parts[3] ?? '',
                        'direccion' => $parts[4] ?? '',
                    ];
                }
            }
            
            fclose($handle);
        }
        
        // Si no está en padrón local, buscar en API SUNAT en tiempo real
        return $this->searchSunatApi($ruc);
    }
    
    private function searchSunatApi($ruc)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sunat.club/ruc/' . $ruc);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'User-Agent: Mozilla/5.0'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                if (isset($data['success']) && $data['success'] === true) {
                    return [
                        'ruc' => $ruc,
                        'razon_social' => $data['data']['razon_social'] ?? $data['data']['nombre'] ?? '',
                        'estado' => $data['data']['estado'] ?? '',
                        'condicion' => $data['data']['condicion'] ?? '',
                        'direccion' => $data['data']['direccion'] ?? $data['data']['domicilio'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error consulta SUNAT API: ' . $e->getMessage());
        }
        
        // Intentar con API alternativa
        return $this->searchSunatApiAlternative($ruc);
    }
    
    private function searchSunatApiAlternative($ruc)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://apis.login.peruapi.com/ruc/' . $ruc);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Authorization: Bearer factuPeruFreeToken'
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if (isset($data['result']) && $data['result'] === 'ok') {
                    return [
                        'ruc' => $ruc,
                        'razon_social' => $data['nombre'] ?? '',
                        'estado' => $data['estado'] ?? '',
                        'direccion' => $data['direccion'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error API alternativa: ' . $e->getMessage());
        }
        
        return null;
    }
}