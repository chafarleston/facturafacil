<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Ubigeo;
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
                    'direccion' => $customer->direccion,
                    'ubigeo' => $ubigeoCodigo,
                ]
            ]);
        }
        
        if (strlen($documento) === 11) {
            $sunatData = $this->searchInSunatPadron($documento);
            if ($sunatData) {
                $ubigeoCodigo = $sunatData['ubigeo'] ?? null;
                $direccionCompleta = $this->concatenateDireccionWithUbigeo($sunatData['direccion'] ?? '', $ubigeoCodigo);
                
                return response()->json([
                    'found' => true,
                    'exists' => false,
                    'api_data' => [
                        'nombre' => $sunatData['razon_social'],
                        'direccion' => $direccionCompleta,
                        'estado' => $sunatData['estado'] ?? '',
                        'condicion' => $sunatData['condicion'] ?? '',
                        'documento_tipo' => '6',
                        'documento_numero' => $documento,
                        'ubigeo' => $ubigeoCodigo,
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
        $filePath = storage_path('app/padron_reducido_ruc.txt');
        
        if (file_exists($filePath)) {
            $handle = fopen($filePath, 'r');
            
            while (($line = fgets($handle)) !== false) {
                $firstPipe = strpos($line, '|');
                if ($firstPipe === false) continue;
                
                $rucInFile = substr($line, 0, $firstPipe);
                
                if ($rucInFile === $ruc) {
                    fclose($handle);
                    
                    $cleanLine = preg_replace('/[\x00-\x1F\x7F]/', '', $line);
                    $parts = explode('|', trim($cleanLine));
                    
                    $direccionParts = [];
                    for ($i = 5; $i < count($parts); $i++) {
                        $part = isset($parts[$i]) ? trim($parts[$i]) : '';
                        if (!empty($part) && $part !== '-' && $part !== '|' && $part !== '') {
                            $direccionParts[] = $part;
                        }
                    }
                    
                    $direccion = implode(' ', $direccionParts);
                    $razonSocial = isset($parts[1]) ? trim($parts[1]) : '';
                    
                    return [
                        'ruc' => $rucInFile,
                        'razon_social' => mb_convert_encoding($razonSocial, 'UTF-8', 'UTF-8'),
                        'estado' => isset($parts[2]) ? trim($parts[2]) : '',
                        'condicion' => isset($parts[3]) ? trim($parts[3]) : '',
                        'ubigeo' => isset($parts[4]) ? trim($parts[4]) : '',
                        'direccion' => $direccion,
                    ];
                }
            }
            
            fclose($handle);
        }
        
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
        
        $ubigeos = Ubigeo::all();
        
        foreach ($ubigeos as $ubigeo) {
            if (strpos($direccion, $ubigeo->distrito) !== false || 
                strpos($direccion, $ubigeo->provincia) !== false ||
                strpos($direccion, $ubigeo->departamento) !== false) {
                return $ubigeo->codigo;
            }
        }
        
        foreach ($ubigeos as $ubigeo) {
            if (strpos($direccion, $ubigeo->provincia) !== false) {
                return $ubigeo->codigo;
            }
        }
        
        return null;
    }
    
    private function concatenateDireccionWithUbigeo($direccion, $ubigeoCodigo)
    {
        if (empty($ubigeoCodigo) || strlen($ubigeoCodigo) !== 6) {
            return $direccion;
        }
        
        $ubigeo = Ubigeo::where('codigo', $ubigeoCodigo)->first();
        
        if (!$ubigeo) {
            return $direccion;
        }
        
        $direccion = trim($direccion);
        
        if (empty($direccion)) {
            return $ubigeo->departamento . ' - ' . $ubigeo->provincia . ' - ' . $ubigeo->distrito;
        }
        
        return $direccion . ' ' . $ubigeo->departamento . ' - ' . $ubigeo->provincia . ' - ' . $ubigeo->distrito;
    }
}