@extends('layouts.app')
@section('title', 'Détail appel')

@section('content')
<h1><i class="fas fa-phone-volume"></i> Détail de l'appel</h1>

<div class="card">
    <h2><i class="fas fa-user"></i> Contact</h2>
    <table>
        <tbody>
            <tr>
                <td style="font-weight:600;width:160px">Nom</td>
                <td>{{ $callLog->contact->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight:600">Numéro</td>
                <td>{{ $callLog->contact->phone }}</td>
            </tr>
            <tr>
                <td style="font-weight:600">Entreprise</td>
                <td>{{ $callLog->contact->company ?? '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight:600">Date de l'appel</td>
                <td>{{ $callLog->called_at?->format('d/m/Y H:i') ?? $callLog->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600">Durée</td>
                <td>{{ $callLog->duration ? $callLog->duration.'s' : '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight:600">SID Twilio</td>
                <td style="font-size:12px;color:#94a3b8">{{ $callLog->call_sid ?? '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight:600">Résultat actuel</td>
                <td>{{ $callLog->result_label }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Transcript IA --}}
<div class="card">
    <h2><i class="fas fa-comments"></i> Transcription de la conversation (IA)</h2>
    @if($callLog->transcript)
        <pre style="white-space:pre-wrap;font-family:inherit;font-size:14px;line-height:1.8;background:#f8fafc;padding:16px;border-radius:10px;border:1px solid #f1f5f9">{{ $callLog->transcript }}</pre>
    @else
        <p style="color:#94a3b8;font-size:14px">Aucune transcription disponible (messagerie, pas de réponse, ou échec).</p>
    @endif
</div>

{{-- Notes manuelles existantes --}}
@if($callLog->notes)
<div class="card">
    <h2><i class="fas fa-sticky-note"></i> Notes</h2>
    <pre style="white-space:pre-wrap;font-family:inherit;font-size:14px;line-height:1.8;background:#fef3c7;padding:16px;border-radius:10px;border:1px solid #fde68a">{{ $callLog->notes }}</pre>
</div>
@endif

{{-- Formulaire pour ajouter une note / corriger le résultat --}}
<div class="card">
    <h2><i class="fas fa-edit"></i> Mettre à jour cet appel</h2>
    <form action="{{ route('calls.note.update', $callLog) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:14px">
            <label><i class="fas fa-flag"></i> Résultat</label>
            <select name="result">
                <option value="">— Ne pas changer —</option>
                <option value="answered"       @selected($callLog->result === 'answered')>✅ Répondu</option>
                <option value="voicemail"      @selected($callLog->result === 'voicemail')>📭 Messagerie</option>
                <option value="no_answer"      @selected($callLog->result === 'no_answer')>📵 Pas de réponse</option>
                <option value="busy"           @selected($callLog->result === 'busy')>🔴 Occupé</option>
                <option value="failed"         @selected($callLog->result === 'failed')>❌ Échec</option>
                <option value="interested"     @selected($callLog->result === 'interested')>🌟 Intéressé</option>
                <option value="not_interested" @selected($callLog->result === 'not_interested')>👎 Pas intéressé</option>
            </select>
        </div>

        <div style="margin-bottom:14px">
            <label><i class="fas fa-pen"></i> Ajouter une note</label>
            <textarea name="notes" rows="4" placeholder="Ex : Le prospect souhaite être rappelé la semaine prochaine, demande un devis détaillé par email..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="{{ route('calls.logs') }}" class="btn btn-gray"><i class="fas fa-arrow-left"></i> Retour à l'historique</a>
    </form>
</div>

@endsection