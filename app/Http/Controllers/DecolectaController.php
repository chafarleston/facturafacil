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
            // Intentar obtener el ubigeo del cliente
            $ubigeoCodigo = $customer->ubigeo;
            if (!$ubigeoCodigo && !empty($customer->direccion)) {
                $ubigeoCodigo = $this->extractUbigeoFromAddress($customer->direccion);
            }
            
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
                    'ubigeo' => $ubigeoCodigo,
                ],
                'api_data' => [
                    'nombre' => $customer->nombre,
                    'direccion' => $customer->direccion ?? '',
                    'ubigeo' => $ubigeoCodigo,
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
                        'ubigeo' => $sunatData['ubigeo'] ?? null,
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
        // Buscar en archivo local del padrón
        $filePath = storage_path('app/padron_reducido_ruc.txt');
        
        if (file_exists($filePath)) {
            $handle = fopen($filePath, 'r');
            
            while (($line = fgets($handle)) !== false) {
                $parts = explode('|', trim($line));
                
                // El RUC debe tener 11 dígitos y coincidir exactamente
                if (isset($parts[0]) && strlen($parts[0]) === 11 && $parts[0] === $ruc) {
                    fclose($handle);
                    
                    // Formato: RUC|razon_social|estado|condicion|ubigeo|direccion...
                    // Posiciones: 0=RUC, 1=razon_social, 2=estado, 3=condicion, 4=ubigeo, 5+=direccion
                    
                    // Concatenar direcciones desde posicion 5
                    $direccionParts = [];
                    for ($i = 5; $i < count($parts); $i++) {
                        if (isset($parts[$i]) && !empty(trim($parts[$i]))) {
                            $direccionParts[] = trim($parts[$i]);
                        }
                    }
                    $direccion = implode(' ', $direccionParts);
                    
                    return [
                        'ruc' => $parts[0] ?? '',
                        'razon_social' => $parts[1] ?? '',
                        'estado' => $parts[2] ?? '',
                        'condicion' => $parts[3] ?? '',
                        'ubigeo' => $parts[4] ?? '',
                        'direccion' => $direccion,
                    ];
                }
            }
            
            fclose($handle);
        }
        
        // Si no está en padrón local, buscar en API SUNAT
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
    
    private function extractUbigeoFromAddress($direccion)
    {
        if (empty($direccion)) {
            return null;
        }
        
        $direccion = strtoupper(trim($direccion));
        
        // Buscar en la tabla de ubigeos por nombre de distrito en la dirección
        $ubigeos = \App\Models\Ubigeo::all();
        
        foreach ($ubigeos as $ubigeo) {
            if (strpos($direccion, $ubigeo->distrito) !== false || 
                strpos($direccion, $ubigeo->provincia) !== false ||
                strpos($direccion, $ubigeo->departamento) !== false) {
                return $ubigeo->codigo;
            }
        }
        
        // Si no encuentra coincidencia, buscar por provincia
        foreach ($ubigeos as $ubigeo) {
            if (strpos($direccion, $ubigeo->provincia) !== false) {
                return $ubigeo->codigo;
            }
        }
        
        return null;
    }
}