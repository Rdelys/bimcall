@extends('layouts.app')
@section('title', 'Historique')

@section('content')
<h1><i class="fas fa-history"></i> Historique des appels</h1>

<div class="card">
    @if($logs->isEmpty())
        <p class="empty-state"><i class="fas fa-inbox"></i> Aucun appel enregistré pour l'instant.</p>
    @else
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Contact</th>
                    <th><i class="fas fa-hashtag"></i> Numéro</th>
                    <th><i class="fas fa-chart-simple"></i> Résultat</th>
                    <th><i class="fas fa-hourglass-half"></i> Durée</th>
                    <th><i class="fas fa-calendar"></i> Date</th>
                    <th><i class="fas fa-info-circle"></i> Détails</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr x-data="{ open: false }">
                    <td data-label="Contact">{{ $log->contact->name ?? '—' }}</td>
                    <td data-label="Numéro">{{ $log->contact->phone }}</td>
                    <td data-label="Résultat">
                        <span class="badge-call-result {{ $log->result }}">
                            {{ $log->result_label }}
                        </span>
                    </td>
                    <td data-label="Durée">{{ $log->duration ? $log->duration.'s' : '—' }}</td>
                    <td data-label="Date" class="date-cell">{{ $log->called_at?->format('d/m/Y H:i') ?? $log->created_at->format('d/m/Y H:i') }}</td>
                    <td data-label="Détails">
                        @if($log->transcript || $log->notes)
                            <button class="btn btn-gray btn-sm" @click="open = !open">
                                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                <span x-text="open ? 'Masquer' : 'Voir'"></span>
                            </button>
                        @else
                            <span class="no-details">—</span>
                        @endif
                    </td>
                </tr>
                @if($log->transcript || $log->notes)
                <tr x-show="open" x-cloak class="details-row">
                    <td colspan="6">
                        <div class="details-container">
                            @if($log->transcript)
                                <div class="detail-section">
                                    <strong class="detail-label"><i class="fas fa-file-alt"></i> Transcription</strong>
                                    <pre class="transcript">{{ $log->transcript }}</pre>
                                </div>
                            @endif
                            @if($log->notes)
                                <div class="detail-section">
                                    <strong class="detail-label"><i class="fas fa-pencil-alt"></i> Notes</strong>
                                    <p class="notes">{{ $log->notes }}</p>
                                </div>
                            @endif
                            <div class="call-sid">
                                <i class="fas fa-fingerprint"></i> SID: {{ $log->call_sid ?? '—' }}
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection

@section('scripts')
<style>
    [x-cloak] { 
        display: none !important; 
    }
    
    /* Header styles */
    h1 {
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    h1 i {
        color: #7c3aed;
        font-size: 1.5rem;
    }
    
    /* Card styles */
    .card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Empty state */
    .empty-state {
        color: #94a3b8;
        font-size: 14px;
        text-align: center;
        padding: 48px 20px;
    }
    
    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.5;
    }
    
    /* Table styles */
    .table-responsive {
        overflow-x: auto;
        margin: 0 -20px;
        padding: 0 20px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    th {
        text-align: left;
        padding: 14px 12px;
        background: #f8fafc;
        font-weight: 600;
        font-size: 12px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    th i {
        margin-right: 6px;
        font-size: 11px;
    }
    
    td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    
    tbody tr {
        transition: background-color 0.2s ease;
    }
    
    tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Badge styles */
    .badge-call-result {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    
    .badge-call-result.success,
    .badge-call-result.done,
    .badge-call-result.interested {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .badge-call-result.pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    
    .badge-call-result.calling {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    
    .badge-call-result.failed {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .badge-call-result.voicemail {
        background: #f3e8ff;
        color: #6b21a5;
        border: 1px solid #e9d5ff;
    }
    
    /* Date cell */
    .date-cell {
        color: #64748b;
        font-size: 13px;
        white-space: nowrap;
    }
    
    /* Button styles */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn-sm {
        padding: 4px 10px;
        font-size: 11px;
    }
    
    .btn-gray {
        background: #e2e8f0;
        color: #475569;
    }
    
    .btn-gray:hover {
        background: #cbd5e1;
        transform: translateY(-1px);
    }
    
    .no-details {
        color: #cbd5e1;
        font-size: 14px;
    }
    
    /* Details row styles */
    .details-row {
        background: #fafafa;
    }
    
    .details-row td {
        padding: 0;
    }
    
    .details-container {
        padding: 20px;
        background: #fafafa;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-section {
        margin-bottom: 16px;
    }
    
    .detail-section:last-of-type {
        margin-bottom: 12px;
    }
    
    .detail-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    
    .detail-label i {
        font-size: 11px;
    }
    
    .transcript {
        white-space: pre-wrap;
        font-family: inherit;
        font-size: 13px;
        line-height: 1.6;
        margin: 0;
        background: white;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .notes {
        font-size: 13px;
        margin: 0;
        background: white;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        line-height: 1.5;
    }
    
    .call-sid {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
    }
    
    .call-sid i {
        margin-right: 4px;
        font-size: 10px;
    }
    
    /* Pagination container */
    .pagination-container {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
    
    .pagination-container nav {
        display: inline-flex;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .card {
            padding: 16px;
        }
        
        h1 {
            font-size: 1.5rem;
        }
        
        th {
            font-size: 11px;
            padding: 10px 8px;
        }
        
        td {
            padding: 10px 8px;
            font-size: 13px;
        }
        
        .badge-call-result {
            font-size: 10px;
            padding: 3px 8px;
        }
        
        .date-cell {
            font-size: 11px;
        }
    }
    
    @media (max-width: 640px) {
        .table-responsive {
            margin: 0 -16px;
            padding: 0 16px;
        }
        
        /* Make table scrollable horizontally */
        table {
            min-width: 600px;
        }
        
        .details-container {
            padding: 16px;
        }
        
        .transcript, .notes {
            font-size: 12px;
            padding: 10px;
        }
    }
    
    @media (max-width: 480px) {
        h1 {
            font-size: 1.25rem;
        }
        
        .card {
            padding: 12px;
        }
        
        .btn-sm {
            padding: 3px 8px;
            font-size: 10px;
        }
        
        .detail-label {
            font-size: 10px;
        }
        
        .transcript, .notes {
            font-size: 11px;
        }
        
        .call-sid {
            font-size: 10px;
        }
    }
    
    /* Optional: Style for pagination if using Bootstrap or custom */
    .pagination {
        display: flex;
        gap: 5px;
        padding: 0;
        list-style: none;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination a, .pagination span {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    
    .pagination a {
        background: #f1f5f9;
        color: #475569;
    }
    
    .pagination a:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }
    
    .pagination .active span {
        background: #7c3aed;
        color: white;
    }
    
    /* Animation for details row */
    .details-row td {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection