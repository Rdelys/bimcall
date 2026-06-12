@extends('layouts.app')
@section('title', 'Offres / IA')

@section('content')
<h1><i class="fas fa-robot"></i> Offres & Prompts IA</h1>

<p class="info-text">
    <i class="fas fa-info-circle"></i> Configurez le message d'ouverture et les instructions données à l'IA pendant l'appel.
    Une seule offre peut être <strong>active</strong> à la fois — c'est celle utilisée lors des appels.
    Vous pouvez utiliser <code>{name}</code> et <code>{company}</code> dans le message d'ouverture, ils seront remplacés par les infos du contact.
</p>

{{-- Liste des prompts existants --}}
<div class="card">
    <h2><i class="fas fa-book"></i> Offres enregistrées</h2>

    @if($prompts->isEmpty())
        <p class="empty-state"><i class="fas fa-inbox"></i> Aucune offre enregistrée. Créez-en une ci-dessous.</p>
    @else
    <div class="table-responsive">
        <table class="prompts-table">
            <thead>
                <tr>
                    <th><i class="fas fa-tag"></i> Nom</th>
                    <th><i class="fas fa-chart-simple"></i> Statut</th>
                    <th><i class="fas fa-comment"></i> Message d'ouverture</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prompts as $prompt)
                <tr>
                    <td data-label="Nom"><strong>{{ $prompt->name }}</strong></td>
                    <td data-label="Statut">
                        @if($prompt->is_active)
                            <span class="badge badge-done"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge badge-pending"><i class="fas fa-clock"></i> Inactive</span>
                        @endif
                    </td>
                    <td data-label="Message d'ouverture" class="opening-message">
                        {{ $prompt->opening_message }}
                    </td>
                    <td data-label="Actions" class="actions-cell">
                        <button class="btn btn-gray btn-sm" onclick="editPrompt({{ $prompt->id }}, {{ json_encode($prompt->name) }}, {{ json_encode($prompt->system_prompt) }}, {{ json_encode($prompt->opening_message) }})">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        @if(!$prompt->is_active)
                        <form action="{{ route('prompts.activate', $prompt) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-green btn-sm">
                                <i class="fas fa-power-off"></i> Activer
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Formulaire création / édition --}}
<div class="card">
    <h2 id="form-title"><i class="fas fa-plus-circle"></i> Nouvelle offre</h2>

    <form action="{{ route('prompts.save') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="prompt-id" value="">

        <div class="form-group">
            <label><i class="fas fa-tag"></i> Nom de l'offre</label>
            <input type="text" name="name" id="prompt-name" placeholder="Ex : Offre internet fibre - Janvier 2026" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-comment-dots"></i> Message d'ouverture (dit dès que l'appel est décroché)</label>
            <textarea name="opening_message" id="prompt-opening" rows="3" required
                placeholder="Bonjour {name}, je suis l'assistant virtuel de la société XYZ. Je vous appelle au sujet d'une offre spéciale sur nos forfaits internet. Avez-vous quelques instants ?"></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-brain"></i> Instructions pour l'IA (system prompt)</label>
            <textarea name="system_prompt" id="prompt-system" rows="8" required
                placeholder="Tu es un commercial téléphonique pour la société XYZ. Tu présentes notre offre fibre optique à 19,99€/mois avec installation gratuite. Sois courtois, à l'écoute, et réponds aux objections sur le prix en mentionnant la promotion de lancement valable ce mois-ci. Ne sois jamais insistant."></textarea>
            <p class="help-text">
                <i class="fas fa-lightbulb"></i> Décrivez l'offre, le ton à adopter, les arguments clés et comment gérer les objections courantes.
                Les règles de fin d'appel (raccrocher, tags) sont déjà gérées automatiquement.
            </p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <button type="button" class="btn btn-gray" onclick="resetForm()">
                <i class="fas fa-times"></i> Annuler
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<style>
    /* Info text */
    .info-text {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .info-text i {
        margin-top: 2px;
        font-size: 14px;
        color: #7c3aed;
    }
    
    .info-text code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        color: #7c3aed;
        font-weight: 600;
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
        -webkit-overflow-scrolling: touch;
    }
    
    .prompts-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        min-width: 500px;
    }
    
    .prompts-table th {
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
    
    .prompts-table th i {
        margin-right: 6px;
        font-size: 11px;
    }
    
    .prompts-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .prompts-table tr:hover td {
        background: #f8fafc;
    }
    
    .opening-message {
        max-width: 300px;
        font-size: 13px;
        color: #64748b;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .actions-cell {
        white-space: nowrap;
    }
    
    .actions-cell form {
        display: inline-block;
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
    
    .badge-done {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    /* Form styles */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-group label i {
        font-size: 12px;
        color: #7c3aed;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 11px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.25s ease;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #7c3aed;
        outline: none;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
    }
    
    .help-text {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 8px;
        display: flex;
        align-items: flex-start;
        gap: 6px;
    }
    
    .help-text i {
        margin-top: 2px;
        font-size: 11px;
        color: #7c3aed;
    }
    
    /* Form actions */
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    
    /* Button styles */
    .btn {
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn i {
        font-size: 14px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
    }
    
    .btn-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
    }
    
    .btn-green:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    .btn-sm i {
        font-size: 11px;
    }
    
    .btn:active {
        transform: translateY(0);
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .info-text {
            font-size: 12px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
        
        .actions-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .actions-cell .btn {
            white-space: nowrap;
        }
    }
    
    @media (max-width: 640px) {
        .table-responsive {
            margin: 0 -16px;
            padding: 0 16px;
        }
        
        .prompts-table {
            min-width: 550px;
        }
        
        .opening-message {
            max-width: 200px;
        }
    }
    
    @media (max-width: 480px) {
        .badge {
            font-size: 10px;
            padding: 3px 8px;
        }
        
        .badge i {
            font-size: 9px;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        .form-group input,
        .form-group textarea {
            padding: 9px 12px;
            font-size: 13px;
        }
        
        .help-text {
            font-size: 11px;
        }
    }
    
    /* Animation */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: slideIn 0.3s ease;
    }
</style>

<script>
function editPrompt(id, name, system, opening) {
    document.getElementById('prompt-id').value = id;
    document.getElementById('prompt-name').value = name;
    document.getElementById('prompt-system').value = system;
    document.getElementById('prompt-opening').value = opening;
    document.getElementById('form-title').innerHTML = '<i class="fas fa-edit"></i> Modifier l\'offre';
    window.scrollTo({ top: document.querySelector('.card:last-child').offsetTop - 20, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('prompt-id').value = '';
    document.getElementById('prompt-name').value = '';
    document.getElementById('prompt-system').value = '';
    document.getElementById('prompt-opening').value = '';
    document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle"></i> Nouvelle offre';
}
</script>
@endsection