<?php

namespace App\Services;

use PDO;

class SunatPadronService
{
    public const RELATIVE_PATH = 'padron/sunat_padron.sqlite';

    private ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }

    public static function path(): string
    {
        return storage_path('app/' . self::RELATIVE_PATH);
    }

    public function databasePath(): string
    {
        return $this->path ?: self::path();
    }

    public static function driverAvailable(): bool
    {
        return in_array('sqlite', PDO::getAvailableDrivers(), true);
    }

    public function exists(): bool
    {
        return self::driverAvailable() && is_file($this->databasePath());
    }

    public static function normalizeRuc(string $ruc): ?string
    {
        $ruc = preg_replace('/\D/', '', $ruc);

        return strlen($ruc) === 11 ? $ruc : null;
    }

    public function findByRuc(string $ruc): ?array
    {
        if (!$this->exists()) {
            return null;
        }

        $ruc = self::normalizeRuc($ruc);
        if (!$ruc) {
            return null;
        }

        $pdo = new PDO('sqlite:' . $this->databasePath(), null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec('PRAGMA query_only = ON');

        $statement = $pdo->prepare(
            'SELECT ruc, razon_social, estado, condicion, ubigeo, tipo_via, nombre_via,
                    codigo_zona, tipo_zona, numero, interior, lote, departamento, manzana,
                    kilometro, direccion_completa
             FROM ruc_padron
             WHERE ruc = :ruc
             LIMIT 1'
        );
        $statement->execute(['ruc' => $ruc]);

        $row = $statement->fetch();

        return $row ?: null;
    }

    public function findManyByRucs(array $rucs): array
    {
        if (!$this->exists()) {
            return [];
        }

        $rucs = array_values(array_unique(array_filter(array_map(
            fn ($ruc) => self::normalizeRuc((string) $ruc),
            $rucs
        ))));

        if (empty($rucs)) {
            return [];
        }

        $rows = [];

        foreach (array_chunk($rucs, 500) as $chunk) {
            $pdo = new PDO('sqlite:' . $this->databasePath(), null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pdo->exec('PRAGMA query_only = ON');

            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $statement = $pdo->prepare(
                'SELECT ruc, razon_social, estado, condicion, ubigeo, tipo_via, nombre_via,
                        codigo_zona, tipo_zona, numero, interior, lote, departamento, manzana,
                        kilometro, direccion_completa
                 FROM ruc_padron
                 WHERE ruc IN (' . $placeholders . ')'
            );
            $statement->execute($chunk);

            foreach ($statement->fetchAll() as $row) {
                $rows[$row['ruc']] = $row;
            }
        }

        return $rows;
    }

    public function stats(): array
    {
        if (!$this->exists()) {
            return [
                'exists' => false,
                'records' => 0,
                'size' => '0 MB',
                'last_modified' => null,
            ];
        }

        $pdo = new PDO('sqlite:' . $this->databasePath(), null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $records = (int) $pdo->query('SELECT COUNT(*) FROM ruc_padron')->fetchColumn();

        return [
            'exists' => true,
            'records' => $records,
            'size' => number_format(filesize($this->databasePath()) / 1024 / 1024, 2) . ' MB',
            'last_modified' => date('d/m/Y H:i:s', filemtime($this->databasePath())),
        ];
    }
}
