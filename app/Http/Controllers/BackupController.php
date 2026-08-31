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

        $mysqldump = $this->findMysqldump();
        $extra = $this->isMysql8Bin($mysqldump) ? ' --skip-column-statistics' : '';

        $command = sprintf(
            '"%s" -h %s -P %s -u %s %s --default-character-set=utf8mb4 --single-transaction --routines --triggers --hex-blob%s %s > %s',
            $mysqldump,
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '-p' . escapeshellarg($password) : '',
            $extra,
            escapeshellarg($database),
            escapeshellarg($path)
        );

        $output = null;
        $returnCode = null;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $errorDetail = implode("\n", array_slice($output, -20));
            \Log::error('Backup failed', ['path' => $path, 'code' => $returnCode, 'out' => $errorDetail]);
            return back()->with('error', 'Error al generar el backup. Código: ' . $returnCode . ' — ' . $errorDetail);
        }

        $size = file_exists($path) ? round(filesize($path) / 1024 / 1024, 2) : 0;

        return back()->with('success', "Backup generado exitosamente: {$path} ({$size} MB)");
    }

    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:204800',
        ]);

        $db = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $file = $request->file('sql_file');
        $tmpPath = $file->getRealPath();

        $mysql = $this->findMysql();

        $command = sprintf(
            '"%s" -h %s -P %s -u %s %s --default-character-set=utf8mb4 %s < %s',
            $mysql,
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($tmpPath)
        );

        $output = null;
        $returnCode = null;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $errorDetail = implode("\n", array_slice($output, -20));
            \Log::error('Restore failed', ['code' => $returnCode, 'out' => $errorDetail]);
            return back()->with('error', 'Error al restaurar la base de datos. Código: ' . $returnCode . ' — ' . $errorDetail);
        }

        return back()->with('success', 'Base de datos restaurada correctamente.');
    }

    private function findMysqldump(): string
    {
        $paths = [
            'C:\\laragon\\bin\\mysql\\mariadb-10.6.27-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
        ];
        foreach ($paths as $p) {
            if (file_exists($p)) {
                return $p;
            }
        }
        return 'mysqldump';
    }

    private function findMysql(): string
    {
        $paths = [
            'C:\\laragon\\bin\\mysql\\mariadb-10.6.27-winx64\\bin\\mysql.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe',
        ];
        foreach ($paths as $p) {
            if (file_exists($p)) {
                return $p;
            }
        }
        return 'mysql';
    }

    private function isMysql8Bin(string $bin): bool
    {
        return str_contains($bin, 'mysql-8');
    }
}