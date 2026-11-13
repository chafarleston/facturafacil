<?php

namespace App\Services;

use PDO;
use RuntimeException;

class SunatPadronImporter
{
    public function import(string $source, ?string $target = null, bool $removeSource = false, ?callable $progress = null): array
    {
        if (!is_file($source) || !is_readable($source)) {
            throw new RuntimeException('No se encontró el archivo del padrón: ' . $source);
        }

        $target = $target ?: SunatPadronService::path();
        $directory = dirname($target);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio del padrón: ' . $directory);
        }

        $temporary = $target . '.tmp-' . getmypid() . '-' . uniqid();

        if (is_file($temporary)) {
            @unlink($temporary);
        }

        set_time_limit(0);

        try {
            $pdo = new PDO('sqlite:' . $temporary, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec('PRAGMA journal_mode = OFF');
            $pdo->exec('PRAGMA synchronous = OFF');
            $pdo->exec('PRAGMA temp_store = MEMORY');

            $pdo->exec(
                'CREATE TABLE ruc_padron (
                    ruc TEXT PRIMARY KEY,
                    razon_social TEXT NOT NULL,
                    estado TEXT,
                    condicion TEXT,
                    ubigeo TEXT,
                    tipo_via TEXT,
                    nombre_via TEXT,
                    codigo_zona TEXT,
                    tipo_zona TEXT,
                    numero TEXT,
                    interior TEXT,
                    lote TEXT,
                    departamento TEXT,
                    manzana TEXT,
                    kilometro TEXT,
                    direccion_completa TEXT,
                    updated_at TEXT
                )'
            );

            $insert = $pdo->prepare(
                'INSERT OR REPLACE INTO ruc_padron
                    (ruc, razon_social, estado, condicion, ubigeo, tipo_via, nombre_via,
                     codigo_zona, tipo_zona, numero, interior, lote, departamento, manzana,
                     kilometro, direccion_completa, updated_at)
                 VALUES
                    (:ruc, :razon_social, :estado, :condicion, :ubigeo, :tipo_via, :nombre_via,
                     :codigo_zona, :tipo_zona, :numero, :interior, :lote, :departamento, :manzana,
                     :kilometro, :direccion_completa, :updated_at)'
            );

            $handle = fopen($source, 'r');
            if (!$handle) {
                throw new RuntimeException('No se pudo abrir el padrón para lectura.');
            }

            fgets($handle); // Cabecera
            $count = 0;
            $batch = 0;
            $now = now()->toDateTimeString();

            $pdo->beginTransaction();

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $parts = array_slice(explode('|', $line), 0, 15);
                if (count($parts) < 5) {
                    continue;
                }

                $ruc = preg_replace('/\D/', '', $parts[0] ?? '');
                if (strlen($ruc) !== 11) {
                    continue;
                }

                $fields = [];
                for ($i = 0; $i < 15; $i++) {
                    $fields[$i] = $this->normalizeText($parts[$i] ?? '');
                }

                $addressParts = [];
                for ($i = 5; $i < 15; $i++) {
                    if ($fields[$i] !== '' && $fields[$i] !== '-') {
                        $addressParts[] = $fields[$i];
                    }
                }

                $insert->execute([
                    ':ruc' => $ruc,
                    ':razon_social' => $fields[1] ?: $ruc,
                    ':estado' => $fields[2],
                    ':condicion' => $fields[3],
                    ':ubigeo' => $fields[4],
                    ':tipo_via' => $fields[5],
                    ':nombre_via' => $fields[6],
                    ':codigo_zona' => $fields[7],
                    ':tipo_zona' => $fields[8],
                    ':numero' => $fields[9],
                    ':interior' => $fields[10],
                    ':lote' => $fields[11],
                    ':departamento' => $fields[12],
                    ':manzana' => $fields[13],
                    ':kilometro' => $fields[14],
                    ':direccion_completa' => implode(' ', $addressParts),
                    ':updated_at' => $now,
                ]);

                $count++;
                $batch++;

                if ($batch >= 25000) {
                    $pdo->commit();
                    $pdo->beginTransaction();
                    $batch = 0;

                    if ($progress) {
                        $progress($count);
                    }
                }
            }

            fclose($handle);
            $pdo->commit();
            $insert = null;
            $pdo = null;
            gc_collect_cycles();

            if ($count === 0) {
                throw new RuntimeException('El padrón no contiene registros válidos.');
            }

            $backup = null;
            if (is_file($target)) {
                $backup = $target . '.bak';
                if (is_file($backup)) {
                    @unlink($backup);
                }

                if (!rename($target, $backup)) {
                    throw new RuntimeException('No se pudo respaldar la base anterior del padrón.');
                }
            }

            if (!rename($temporary, $target)) {
                if ($backup && is_file($backup)) {
                    @rename($backup, $target);
                }
                throw new RuntimeException('No se pudo reemplazar la base del padrón.');
            }

            if ($removeSource) {
                @unlink($source);
            }

            return [
                'success' => true,
                'records' => $count,
                'path' => $target,
                'backup' => $backup,
                'size' => number_format(filesize($target) / 1024 / 1024, 2) . ' MB',
            ];
        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            @unlink($temporary);

            throw $e;
        }
    }

    private function normalizeText(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        return trim($value);
    }
}
