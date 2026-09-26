<div class="modal fade" id="sunatAlertModal" tabindex="-1" role="dialog" aria-labelledby="sunatAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="sunatAlertModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Alerta de Facturación SUNAT
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @php
                    $sunatAlerts = \App\Models\SunatAlert::active()->get();
                @endphp
                @forelse($sunatAlerts as $alert)
                    <div class="alert alert-warning">
                        <strong>Problema con la facturación:</strong>
                        <p class="mb-1">{{ $alert->descripcion }}</p>
                        <form action="{{ route('sunat-alerts.resolve', $alert) }}" method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-check"></i> Marcar como resuelto
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">No hay alertas activas.</p>
                @endforelse
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>