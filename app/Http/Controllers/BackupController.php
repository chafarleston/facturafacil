<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        $defaultPath = storage_path('app/backup/facturafacil_' . now()->format('Ymd_His') . '.sql');
        return view('backup.index', compact('defaultPath'));
    }

    public function run(Request $request)
    {
        $request->validate([
            'path' => 'required|string|max:500',
        ]);

        $path = $request->input('path');
        $dir = dirname($path);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return back()->with('error', 'No se pudo crear el directorio: ' . $dir);
            }
        }

        $db = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $command = sprintf(
            '"%s" -h %s -P %s -u %s %s %s > "%s"',
            $this->findMysqldump(),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            $path
        );

        $output = null;
        $returnCode = null;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            \Log::error('Backup failed', ['path' => $path, 'code' => $returnCode]);
            return back()->with('error', 'Error al generar el backup. Código: ' . $returnCode);
        }

        $size = file_exists($path) ? round(filesize($path) / 1024 / 1024, 2) : 0;

        return back()->with('success', "Backup generado exitosamente: {$path} ({$size} MB)");
    }

    private function findMysqldump(): string
    {
        $possiblePaths = [
            'mysqldump',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mariadb-10.6.27-winx64\\bin\\mysqldump.exe',
        ];

        foreach ($possiblePaths as $p) {
            if (file_exists($p) || $p === 'mysqldump') {
                return $p;
            }
        }

        return 'mysqldump';
    }
}
