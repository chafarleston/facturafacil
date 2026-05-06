<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc', 'razon_social', 'nombre_comercial', 'direccion',
        'departamento', 'provincia', 'distrito', 'ubigeo',
        'telefono', 'email', 'logo', 'certificado_path',
        'certificado_password', 'certificado_vence',
        'tipo_contribuyente', 'estado',
        'soap_type_id', 'soap_username', 'soap_password', 'certificate'
    ];

    public function hasCertificate()
    {
        return $this->certificate && file_exists(storage_path('app/certificates/' . $this->certificate));
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getTipoContribuyenteNombre()
    {
        $tipos = [
            '01' => 'Persona Natural sin Negocio',
            '02' => 'Persona Natural con Negocio',
            '03' => 'Sociedad Conyugal sin Negocio',
            '04' => 'Sociedad Conyugal con Negocio',
            '05' => 'Sucesión Indivisa sin Negocio',
            '06' => 'Sucesión Indivisa con Negocio',
            '07' => 'Empresa Individual de Resp. Ltda',
            '08' => 'Sociedad Civil',
            '09' => 'Sociedad Irregular',
            '10' => 'Asociación en Participación',
            '11' => 'Asociación',
            '12' => 'Fundación',
            '13' => 'Sociedad en Comandita Simple',
            '14' => 'Sociedad Colectiva',
            '15' => 'Instituciones Públicas',
            '16' => 'Instituciones Religiosas',
            '17' => 'Sociedad de Beneficencia',
            '18' => 'Entidades de Auxilio Mutuo',
            '19' => 'Universidad, Centros Educativos',
            '20' => 'Gobierno Regional/Local',
            '21' => 'Gobierno Central',
            '22' => 'Comunidad Laboral',
            '23' => 'Comunidad Campesina',
            '24' => 'Cooperativas',
            '25' => 'Empresa de Propiedad Social',
            '26' => 'Sociedad Anónima',
            '27' => 'Sociedad en Comandita por Acciones',
            '28' => 'Sociedad Com.Resp. Ltda',
            '29' => 'Sucursal Empresa Extranjera',
            '30' => 'Empresa de Derecho Público',
            '31' => 'Empresa Estatal de Derecho Privado',
            '32' => 'Empresa de Economía Mixta',
            '33' => 'Accionariado del Estado',
            '34' => 'Misiones Diplomáticas',
            '35' => 'Junta de Propietarios',
            '36' => 'Oficina de Representación',
            '37' => 'Fondos Mutuos de Inversión',
            '38' => 'Sociedad Anónima Abierta',
            '39' => 'Sociedad Anónima Cerrada',
            '40' => 'Contratos de Colaboración',
            '41' => 'Entidad Coop.Técnica',
            '42' => 'Comunidad de Bienes',
            '43' => 'Sociedad Minera de Resp. Ltda',
            '44' => 'Asociación No Inscritos',
            '45' => 'Partidos Políticos',
            '46' => 'Asociación de Hecho',
            '47' => 'CAFAES',
            '48' => 'Sindicatos',
            '49' => 'Colegios Profesionales',
            '50' => 'Comités Inscritos',
        ];
        return $tipos[$this->tipo_contribuyente] ?? $this->tipo_contribuyente;
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function series()
    {
        return $this->hasMany(Serie::class);
    }
    
    public static function getMainCompany()
    {
        $company = self::where('is_main', true)->first();
        if (!$company) {
            $company = self::where('estado', 'ACTIVO')->first();
        }
        return $company;
    }
}