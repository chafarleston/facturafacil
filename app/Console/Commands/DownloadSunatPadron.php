<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class DownloadSunatPadron extends Command
{
    protected $signature = 'sunat:download-padron';
    protected $description = 'Descarga el padrón reducido de SUNAT';

    public function handle()
    {
        $this->info('Descargando padrón reducido de SUNAT...');
        
        $zipUrl = 'http://www2.sunat.gob.pe/padron_reducido_ruc.zip';
        $zipPath = storage_path('app/sunat_padron.zip');
        $extractPath = storage_path('app/');
        
        try {
            $this->info('Descargando archivo ZIP...');
            $response = Http::timeout(600)->get($zipUrl);
            
            if ($response->successful() && strlen($response->body()) > 1000) {
                File::put($zipPath, $response->body());
                
                $this->info('Archivo ZIP descargado: ' . number_format(filesize($zipPath)/1024/1024, 2) . ' MB');
                
                $this->info('Extrayendo archivo...');
                $zip = new ZipArchive;
                if ($zip->open($zipPath) === TRUE) {
                    $zip->extractTo($extractPath);
                    $zip->close();
                    
                    $files = glob(storage_path('app/*.txt'));
                    foreach ($files as $file) {
                        if (stripos($file, 'padron') !== false || stripos($file, 'ruc') !== false) {
                            $fp = fopen($file, 'r');
                            $lines = 0;
                            while (!feof($fp)) {
                                fgets($fp);
                                $lines++;
                            }
                            fclose($fp);
                            $this->info('Padrón encontrado: ' . basename($file) . ' (' . number_format($lines) . ' registros)');
                            $this->info('Proceso completado correctamente');
                            return 0;
                        }
                    }
                    $this->info('Archivo extraído. Buscando archivos...');
                }
            } else {
                $this->error('Error al descargar');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
        
        return 0;
    }
}