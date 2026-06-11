@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
<h1>📊 Tableau de bord BimCall</h1>

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="value">{{ $stats['total'] }}</div>
        <div class="label">Total contacts</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #f59e0b">{{ $stats['pending'] }}</div>
        <div class="label">En attente</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #3b82f6">{{ $stats['calling'] }}</div>
        <div class="label">En cours</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #10b981">{{ $stats['done'] }}</div>
        <div class="label">Traités</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #10b981">{{ $stats['interested'] }}</div>
        <div class="label">Intéressés ⭐</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #64748b">{{ $stats['voicemail'] }}</div>
        <div class="label">Messageries</div>
    </div>
</div>

@if(!$activePrompt)
    <div class="alert alert-warning">
        ⚠️ Aucune offre active ! 
        <a href="{{ route('prompts.index') }}" style="color: inherit; font-weight: 700; text-decoration: underline;">Configurer une offre →</a>
    </div>
@else
    <div class="alert alert-info">
        🤖 Offre active : <strong>{{ $activePrompt->name }}</strong>
    </div>
@endif

<div class="dashboard-grid">
    {{-- Appel manuel --}}
    <div class="card">
        <h2>📱 Appel manuel</h2>
        <form action="{{ route('call.single') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Numéro de téléphone</label>
                <input type="text" name="phone" placeholder="+261340000000" required>
            </div>
            <div class="form-group">
                <label>Nom (optionnel)</label>
                <input type="text" name="name" placeholder="Jean Dupont">
            </div>
            <button type="submit" class="btn btn-primary btn-block" {{ !$activePrompt ? 'disabled' : '' }}>
                📞 Appeler maintenant
            </button>
        </form>
    </div>

    {{-- Campagne automatique --}}
    <div class="card" x-data="{ calling: false, info: null }" x-init="poll()">
        <h2>🚀 Campagne automatique</h2>
        <p class="campaign-info">
            Appelle les contacts de la liste un par un, dans l'ordre. Attendez la fin de chaque appel avant de lancer le suivant.
        </p>

        {{-- Statut live --}}
        <div x-show="info && info.calling" class="live-status">
            <span class="live-dot"></span>
            <strong style="margin-left: 6px">Appel en cours :</strong>
            <span x-text="info?.calling?.name || info?.calling?.phone"></span>
        </div>

        <div class="campaign-actions">
            <form action="{{ route('call.next') }}" method="POST" style="flex: 1;">
                @csrf
                <button type="submit" class="btn btn-green btn-block" {{ !$activePrompt ? 'disabled' : '' }}>
                    ▶ Prochain contact (<span x-text="info?.pending ?? '{{ $stats['pending'] }}'"></span>)
                </button>
            </form>
        </div>

        <script>
        function poll() {
            const self = this;
            setInterval(async () => {
                try {
                    const r = await fetch('/api/call-status');
                    self.info = await r.json();
                } catch(e) {
                    console.error('Polling error:', e);
                }
            }, 3000);
        }
        </script>
    </div>
</div>

{{-- Import --}}
<div class="card">
    <h2>📂 Importer des contacts (Excel / CSV)</h2>
    <p class="import-info">
        Colonnes acceptées : 
        <code>phone</code> (obligatoire), <code>name</code>, <code>company</code>
        ou en français : <code>telephone</code>, <code>nom</code>, <code>entreprise</code>
    </p>
    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <div class="field">
                <label>Fichier Excel ou CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
            </div>
            <button type="submit" class="btn btn-primary">📥 Importer</button>
        </div>
    </form>
</div>

{{-- Derniers appels --}}
<div class="card">
    <h2>📋 Derniers appels</h2>
    @if($recentCalls->isEmpty())
        <p class="empty-state">Aucun appel pour l'instant.</p>
    @else
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Numéro</th>
                    <th>Résultat</th>
                    <th>Durée</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentCalls as $log)
                <tr>
                    <td>{{ $log->contact->name ?? '—' }}</td>
                    <td>{{ $log->contact->phone }}</td>
                    <td>
                        <span class="badge-call-result {{ $log->result }}">
                            {{ $log->result_label }}
                        </span>
                    </td>
                    <td>{{ $log->duration ? $log->duration.'s' : '—' }}</td>
                    <td class="date-cell">{{ $log->created_at->format('d/m H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="view-all">
        <a href="{{ route('calls.logs') }}" class="btn btn-gray btn-sm">Voir tout →</a>
    </div>
    @endif
</div>

<style>
    /* Dashboard specific styles */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .form-group input:focus {
        border-color: #7c3aed;
        outline: none;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }
    
    .btn-block {
        width: 100%;
        justify-content: center;
    }
    
    .campaign-info {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 16px;
        line-height: 1.5;
    }
    
    .live-status {
        background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 16px;
        font-size: 14px;
        border-left: 3px solid #3b82f6;
        animation: fadeIn 0.3s ease;
    }
    
    .campaign-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .import-info {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 16px;
        line-height: 1.5;
    }
    
    .import-info code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        color: #7c3aed;
        font-weight: 600;
    }
    
    .empty-state {
        color: #94a3b8;
        font-size: 14px;
        text-align: center;
        padding: 32px 20px;
    }
    
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
    
    .date-cell {
        color: #64748b;
        font-size: 13px;
        white-space: nowrap;
    }
    
    .view-all {
        margin-top: 16px;
        text-align: right;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .form-row {
            flex-direction: column;
        }
        
        .form-row .field {
            width: 100%;
        }
        
        .form-row .btn {
            width: 100%;
        }
        
        .campaign-actions {
            flex-direction: column;
        }
        
        .campaign-actions form {
            width: 100%;
        }
        
        .view-all {
            text-align: center;
        }
    }
    
    @media (max-width: 480px) {
        .stat-grid {
            gap: 12px;
        }
        
        .live-status {
            font-size: 12px;
            padding: 10px 12px;
        }
        
        .badge-call-result {
            font-size: 10px;
            padding: 3px 8px;
        }
        
        .date-cell {
            font-size: 11px;
        }
    }
    
    /* Disabled button style */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Loading animation for buttons */
    .btn.btn-block {
        position: relative;
    }
    
    /* Hover effects for table rows */
    tbody tr {
        transition: background-color 0.2s ease;
    }
    
    tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Responsive table handling */
    @media (max-width: 640px) {
        .table-responsive {
            margin: 0 -16px;
            padding: 0 16px;
        }
        
        table {
            font-size: 12px;
        }
        
        th, td {
            padding: 10px 8px;
        }
    }
</style>
@endsection