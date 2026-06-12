@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
<h1><i class="fas fa-chart-line"></i> Tableau de bord BimCall</h1>

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="value">{{ $stats['total'] }}</div>
        <div class="label"><i class="fas fa-users"></i> Total contacts</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #f59e0b">{{ $stats['pending'] }}</div>
        <div class="label"><i class="fas fa-clock"></i> En attente</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #3b82f6">{{ $stats['calling'] }}</div>
        <div class="label"><i class="fas fa-phone-alt"></i> En cours</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #10b981">{{ $stats['done'] }}</div>
        <div class="label"><i class="fas fa-check-circle"></i> Traités</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #10b981">{{ $stats['interested'] }}</div>
        <div class="label"><i class="fas fa-star"></i> Intéressés</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color: #64748b">{{ $stats['voicemail'] }}</div>
        <div class="label"><i class="fas fa-voicemail"></i> Messageries</div>
    </div>
</div>

@if(!$activePrompt)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Aucune offre active ! 
        <a href="{{ route('prompts.index') }}" style="color: inherit; font-weight: 700; text-decoration: underline;">Configurer une offre →</a>
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-robot"></i> Offre active : <strong>{{ $activePrompt->name }}</strong>
    </div>
@endif

<div class="dashboard-grid">
    {{-- Appel manuel --}}
    <div class="card">
        <h2><i class="fas fa-mobile-alt"></i> Appel manuel</h2>
        <form action="{{ route('call.single') }}" method="POST">
            @csrf
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Numéro de téléphone</label>
                <input type="text" name="phone" placeholder="+261340000000" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom (optionnel)</label>
                <input type="text" name="name" placeholder="Jean Dupont">
            </div>
            <button type="submit" class="btn btn-primary btn-block" {{ !$activePrompt ? 'disabled' : '' }}>
                <i class="fas fa-phone-alt"></i> Appeler maintenant
            </button>
        </form>
    </div>

    {{-- Campagne automatique --}}
    <div class="card" x-data="{ calling: false, info: null }" x-init="poll()">
        <h2><i class="fas fa-rocket"></i> Campagne automatique</h2>
        <p class="campaign-info">
            <i class="fas fa-info-circle"></i> Appelle les contacts de la liste un par un, dans l'ordre. Attendez la fin de chaque appel avant de lancer le suivant.
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
                    <i class="fas fa-play"></i> Prochain contact (<span x-text="info?.pending ?? '{{ $stats['pending'] }}'"></span>)
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
    <h2><i class="fas fa-file-import"></i> Importer des contacts (Excel / CSV)</h2>
    <p class="import-info">
        <i class="fas fa-info-circle"></i> Colonnes acceptées : 
        <code>phone</code> (obligatoire), <code>name</code>, <code>company</code>
        ou en français : <code>telephone</code>, <code>nom</code>, <code>entreprise</code>
    </p>
    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <div class="field">
                <label><i class="fas fa-file-excel"></i> Fichier Excel ou CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Importer</button>
        </div>
    </form>
</div>

{{-- Derniers appels --}}
<div class="card">
    <h2><i class="fas fa-history"></i> Derniers appels</h2>
    @if($recentCalls->isEmpty())
        <p class="empty-state"><i class="fas fa-inbox"></i> Aucun appel pour l'instant.</p>
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
                </tr>
            </thead>
            <tbody>
                @foreach($recentCalls as $log)
                <tr>
                    <td data-label="Contact">{{ $log->contact->name ?? '—' }}</td>
                    <td data-label="Numéro">{{ $log->contact->phone }}</td>
                    <td data-label="Résultat">
                        <span class="badge-call-result {{ $log->result }}">
                            {{ $log->result_label }}
                        </span>
                    </td>
                    <td data-label="Durée">{{ $log->duration ? $log->duration.'s' : '—' }}</td>
                    <td data-label="Date" class="date-cell">{{ $log->created_at->format('d/m H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="view-all">
        <a href="{{ route('calls.logs') }}" class="btn btn-gray btn-sm"><i class="fas fa-eye"></i> Voir tout →</a>
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
    
    .form-group label i {
        margin-right: 6px;
        font-size: 12px;
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
    
    .campaign-info i {
        margin-right: 6px;
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
    
    .live-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        background: #3b82f6;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
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
    
    .import-info i {
        margin-right: 6px;
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
    
    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.5;
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
    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
    }
    
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
        
        h1 {
            font-size: 1.5rem;
        }
        
        h2 {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 640px) {
        .table-responsive {
            margin: 0 -16px;
            padding: 0 16px;
            overflow-x: auto;
        }
        
        table {
            font-size: 12px;
            width: 100%;
            min-width: 500px;
        }
        
        th, td {
            padding: 10px 8px;
        }
        
        /* Stack stat cards on very small screens */
        .stat-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        
        .stat-card {
            padding: 12px;
        }
        
        .stat-card .value {
            font-size: 1.5rem;
        }
        
        .stat-card .label {
            font-size: 10px;
        }
    }
    
    @media (max-width: 480px) {
        .stat-grid {
            gap: 10px;
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
        
        .alert {
            padding: 10px 12px;
            font-size: 13px;
        }
        
        .card {
            padding: 16px;
        }
    }
    
    /* Disabled button style */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Button base styles */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn i {
        font-size: 14px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .btn-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-green:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    
    .btn-gray {
        background: #e2e8f0;
        color: #475569;
    }
    
    .btn-gray:hover {
        background: #cbd5e1;
        transform: translateY(-1px);
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    /* Card styles */
    .card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .card h2 {
        margin: 0 0 16px 0;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .card h2 i {
        color: #7c3aed;
    }
    
    /* Alert styles */
    .alert {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .alert i {
        font-size: 18px;
    }
    
    .alert-warning {
        background: #fef3c7;
        border-left: 3px solid #f59e0b;
        color: #92400e;
    }
    
    .alert-info {
        background: #dbeafe;
        border-left: 3px solid #3b82f6;
        color: #1e40af;
    }
    
    /* Stat grid styles */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card .value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .stat-card .label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .stat-card .label i {
        margin-right: 4px;
    }
    
    /* Form row */
    .form-row {
        display: flex;
        gap: 16px;
        align-items: flex-end;
    }
    
    .form-row .field {
        flex: 1;
    }
    
    .form-row .field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }
    
    .form-row input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }
    
    /* Table styles */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th {
        text-align: left;
        padding: 12px 8px;
        background: #f8fafc;
        font-weight: 600;
        font-size: 12px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    th i {
        margin-right: 6px;
    }
    
    td {
        padding: 12px 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Mobile table with data labels */
    @media (max-width: 640px) {
        table {
            min-width: 500px;
        }
    }
</style>
@endsection