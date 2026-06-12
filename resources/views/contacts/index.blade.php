@extends('layouts.app')
@section('title', 'Contacts')

@section('content')
<h1><i class="fas fa-address-book"></i> Contacts</h1>

<div class="card">
    @if($contacts->isEmpty())
        <p class="empty-state">
            <i class="fas fa-users-slash"></i> 
            Aucun contact. Importez une liste depuis le <a href="{{ route('home') }}">Dashboard</a>.
        </p>
    @else
    <div class="table-responsive">
        <table class="contacts-table">
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Nom</th>
                    <th><i class="fas fa-phone"></i> Numéro</th>
                    <th><i class="fas fa-building"></i> Entreprise</th>
                    <th><i class="fas fa-chart-simple"></i> Statut</th>
                    <th><i class="fas fa-history"></i> Dernier résultat</th>
                    <th><i class="fas fa-phone-alt"></i> Appels</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                @php $last = $contact->callLogs->sortByDesc('created_at')->first(); @endphp
                <tr class="contact-row" data-status="{{ $contact->status }}">
                    <td data-label="Nom">
                        @if($contact->name)
                            <strong>{{ $contact->name }}</strong>
                        @else
                            <span class="no-name">—</span>
                        @endif
                    </td>
                    <td data-label="Numéro" class="phone-cell">{{ $contact->phone }}</td>
                    <td data-label="Entreprise">{{ $contact->company ?? '—' }}</td>
                    <td data-label="Statut">
                        @switch($contact->status)
                            @case('pending')
                                <span class="badge badge-pending">
                                    <i class="fas fa-clock"></i> En attente
                                </span>
                                @break
                            @case('calling')
                                <span class="badge badge-calling">
                                    <span class="live-dot"></span> En cours
                                </span>
                                @break
                            @case('done')
                                <span class="badge badge-done">
                                    <i class="fas fa-check-circle"></i> Terminé
                                </span>
                                @break
                            @case('failed')
                                <span class="badge badge-failed">
                                    <i class="fas fa-exclamation-triangle"></i> Échec
                                </span>
                                @break
                        @endswitch
                    </td>
                    <td data-label="Dernier résultat">
                        @if($last)
                            <span class="result-badge result-{{ $last->result }}">
                                <i class="fas {{ $last->result == 'interested' ? 'fa-star' : ($last->result == 'done' ? 'fa-check' : 'fa-phone') }}"></i>
                                {{ $last->result_label }}
                            </span>
                        @else
                            <span class="no-result">—</span>
                        @endif
                    </td>
                    <td data-label="Appels" class="calls-count">
                        <span class="count-badge">{{ $contact->callLogs->count() }}</span>
                    </td>
                    <td data-label="Actions" class="actions-cell">
                        <div class="action-buttons">
                            @if($contact->status !== 'pending')
                            <form action="{{ route('contacts.reset', $contact) }}" method="POST" class="inline-form">
                                @csrf
                                <button type="submit" class="btn btn-gray btn-sm" title="Remettre en attente pour le rappeler">
                                    <i class="fas fa-redo-alt"></i> Recontacter
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('contacts.delete', $contact) }}" method="POST" class="inline-form"
                                  onsubmit="return confirm('Supprimer ce contact et son historique ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-red btn-sm" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $contacts->links() }}
    </div>
    @endif
</div>

@endsection

@section('scripts')
<style>
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
    
    .empty-state a {
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
    }
    
    .empty-state a:hover {
        text-decoration: underline;
    }
    
    /* Table styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .contacts-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        min-width: 700px;
    }
    
    .contacts-table th {
        text-align: left;
        padding: 14px 12px;
        background: #f8fafc;
        font-weight: 700;
        font-size: 12px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .contacts-table th i {
        margin-right: 6px;
        font-size: 11px;
    }
    
    .contacts-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .contacts-table tr:hover td {
        background: #f8fafc;
    }
    
    .no-name {
        color: #cbd5e1;
    }
    
    .phone-cell {
        font-family: 'SF Mono', 'Monaco', 'Cascadia Code', monospace;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
    }
    
    /* Badge styles */
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.3px;
    }
    
    .badge i {
        font-size: 11px;
    }
    
    .badge-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    
    .badge-calling {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    
    .badge-done {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .badge-failed {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    /* Result badge */
    .result-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
    }
    
    .result-badge i {
        font-size: 10px;
    }
    
    .result-interested {
        background: #fef3c7;
        color: #92400e;
    }
    
    .result-interested i {
        color: #f59e0b;
    }
    
    .result-done {
        background: #d1fae5;
        color: #065f46;
    }
    
    .result-voicemail {
        background: #f3e8ff;
        color: #6b21a5;
    }
    
    .no-result {
        color: #cbd5e1;
        font-size: 13px;
    }
    
    /* Calls count */
    .calls-count {
        text-align: center;
    }
    
    .count-badge {
        display: inline-block;
        min-width: 28px;
        padding: 2px 8px;
        background: #e2e8f0;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-align: center;
    }
    
    /* Actions */
    .actions-cell {
        white-space: nowrap;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .inline-form {
        display: inline-block;
    }
    
    /* Button styles */
    .btn {
        padding: 6px 12px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    
    .btn i {
        font-size: 11px;
    }
    
    .btn-gray {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    
    .btn-gray:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }
    
    .btn-red {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    
    .btn-red:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }
    
    .btn-sm {
        padding: 4px 10px;
    }
    
    /* Live dot */
    .live-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #3b82f6;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
        margin-right: 4px;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.2);
        }
    }
    
    /* Pagination */
    .pagination-container {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .contacts-table {
            min-width: 600px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            justify-content: center;
        }
        
        .badge {
            font-size: 10px;
            padding: 3px 8px;
        }
        
        .badge i {
            font-size: 9px;
        }
        
        .result-badge {
            font-size: 10px;
            padding: 2px 8px;
        }
    }
    
    @media (max-width: 640px) {
        .table-responsive {
            margin: 0 -16px;
            padding: 0 16px;
        }
        
        .contacts-table {
            min-width: 550px;
        }
        
        .contacts-table th,
        .contacts-table td {
            padding: 10px 8px;
            font-size: 12px;
        }
        
        .phone-cell {
            font-size: 12px;
        }
        
        .count-badge {
            font-size: 11px;
            min-width: 24px;
            padding: 2px 6px;
        }
    }
    
    @media (max-width: 480px) {
        .btn-sm {
            padding: 3px 8px;
            font-size: 10px;
        }
        
        .btn-sm i {
            font-size: 9px;
        }
        
        .badge {
            font-size: 9px;
            padding: 2px 6px;
        }
        
        .result-badge {
            font-size: 9px;
            padding: 2px 6px;
        }
        
        .calls-count .count-badge {
            font-size: 10px;
            min-width: 20px;
        }
    }
    
    /* Alternating row colors for better readability */
    @media (max-width: 640px) {
        .contacts-table tbody tr:nth-child(even) td {
            background-color: #fafafa;
        }
    }
    
    /* Animation for status changes */
    .contact-row {
        transition: background-color 0.2s ease;
    }
    
    /* Tooltip on hover for action buttons */
    .btn[title] {
        position: relative;
    }
    
    /* Loading state for buttons */
    .btn:active {
        transform: translateY(0);
    }
    
    /* Responsive table with data labels for very small screens */
    @media (max-width: 480px) {
        .contacts-table {
            min-width: 500px;
        }
    }
    
    /* Pagination styling if using Bootstrap or custom */
    .pagination {
        display: flex;
        gap: 5px;
        padding: 0;
        list-style: none;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination a, .pagination span {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
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
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
    }
</style>
@endsection