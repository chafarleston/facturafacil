@extends('layouts.admin')
@section('title', 'Venta Procesada')
@section('page_title', 'Venta Procesada')

@push('styles')
<style>
    body { overflow: hidden; }
    .main-footer, .content-header { display: none !important; }
    .content-wrapper { padding-top: 0 !important; }
    
    .success-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: calc(100vh - 60px);
        padding: 20px;
    }
    
    .success-card {
        background: #fff;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        max-width: 500px;
        width: 100%;
    }
    
    .success-icon {
        font-size: 80px;
        color: #28a745;
        margin-bottom: 20px;
    }
    
    .success-title {
        font-size: 28px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    
    .success-invoice {
        font-size: 24px;
        color: #007bff;
        font-weight: bold;
        margin-bottom: 15px;
    }
    
    .success-total {
        font-size: 36px;
        color: #28a745;
        font-weight: bold;
        margin-bottom: 30px;
    }
    
    .btn-group-custom {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-custom {
        padding: 15px 25px;
        font-size: 16px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-sunat {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }
    
    .btn-a4 {
        background: linear-gradient(135deg, #6c757d, #545b62);
        color: white;
    }
    
    .btn-80mm {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }
    
    .btn-new-sale {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .btn-custom:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    
    .btn-custom:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-new-sale-container {
        margin-top: 20px;
    }
    
    .customer-info {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="success-container">
    <div class="success-card">
        <i class="fas fa-check-circle success-icon"></i>
        <div class="success-title">¡Venta Procesada!</div>
        <div class="success-invoice">{{ $invoice->full_number }}</div>
        <div class="customer-info">
            @if($invoice->customer)
                {{ $invoice->customer->nombre }}
            @else
                Cliente Varios
            @endif
            <br>
            {{ $invoice->metodo_pago }} @if($invoice->referencia_pago) - {{ $invoice->referencia_pago }} @endif
        </div>
        <div class="success-total">S/ {{ number_format($invoice->total, 2) }}</div>
        
        <div class="btn-group-custom">
            <button class="btn-custom btn-sunat" onclick="sendToSunat({{ $invoice->id }})" id="btnSunat">
                <i class="fas fa-paper-plane"></i> Enviar a SUNAT
            </button>
            <button class="btn-custom btn-a4" onclick="printInvoice({{ $invoice->id }}, 'A4')">
                <i class="fas fa-file-alt"></i> A4
            </button>
            <button class="btn-custom btn-80mm" onclick="printInvoice({{ $invoice->id }}, '80mm')">
                <i class="fas fa-receipt"></i> 80mm
            </button>
        </div>
        
        <div class="btn-new-sale-container">
            <a href="{{ route('pos.index') }}" class="btn-custom btn-new-sale">
                <i class="fas fa-plus"></i> Nueva Venta
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function sendToSunat(invoiceId) {
    var btn = document.getElementById('btnSunat');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    
    fetch('/pos/sunat/' + invoiceId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            btn.innerHTML = '<i class="fas fa-check"></i> Enviado';
            btn.classList.remove('btn-sunat');
            btn.classList.add('btn-success');
        } else {
            alert(data.message || 'Error al enviar a SUNAT');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar a SUNAT';
        }
    })
    .catch(error => {
        alert('Error al enviar a SUNAT: ' + error);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar a SUNAT';
    });
}

function printInvoice(invoiceId, format) {
    window.open('/pos/print/' + invoiceId + '/' + format, '_blank');
}
</script>
@endpush