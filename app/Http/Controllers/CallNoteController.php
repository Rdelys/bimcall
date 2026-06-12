<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CallLog;

class CallNoteController extends Controller
{
    /**
     * Afficher le détail d'un appel avec son transcript et ses notes
     */
    public function show(CallLog $callLog)
    {
        $callLog->load('contact');
        return view('calls.show', compact('callLog'));
    }

    /**
     * Ajouter / mettre à jour une note manuelle sur un appel
     */
    public function update(Request $request, CallLog $callLog)
    {
        $request->validate([
            'notes'  => 'nullable|string',
            'result' => 'nullable|in:answered,voicemail,no_answer,busy,failed,interested,not_interested',
        ]);

        $data = [];

        if ($request->filled('notes')) {
            // Ajoute la note manuelle en l'horodatant, sans écraser les notes existantes
            $timestamp = now()->format('d/m/Y H:i');
            $newNote   = "[$timestamp] " . $request->notes;

            $data['notes'] = $callLog->notes
                ? $callLog->notes . "\n" . $newNote
                : $newNote;
        }

        if ($request->filled('result')) {
            $data['result'] = $request->result;

            // Si on marque l'appel comme traité, mettre à jour le statut du contact
            if (in_array($request->result, ['interested', 'not_interested', 'answered', 'voicemail', 'busy', 'failed'])) {
                $callLog->contact->update(['status' => 'done']);
            }
        }

        if (!empty($data)) {
            $callLog->update($data);
        }

        return back()->with('success', 'Appel mis à jour avec succès.');
    }
}