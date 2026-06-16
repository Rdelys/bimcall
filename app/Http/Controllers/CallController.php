<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\CallLog;
use App\Models\OfferPrompt;
use App\Services\TelnyxService;

class CallController extends Controller
{
    public function __construct(protected TelnyxService $telnyx) {}

    /**
     * Dashboard principal
     */
    public function index()
    {
        $stats = [
            'total'        => Contact::count(),
            'pending'      => Contact::where('status', 'pending')->count(),
            'done'         => Contact::where('status', 'done')->count(),
            'calling'      => Contact::where('status', 'calling')->count(),
            'interested'   => CallLog::where('result', 'interested')->count(),
            'voicemail'    => CallLog::where('result', 'voicemail')->count(),
        ];

        $recentCalls = CallLog::with('contact')->latest()->limit(10)->get();
        $activePrompt = OfferPrompt::getActive();

        return view('calls.index', compact('stats', 'recentCalls', 'activePrompt'));
    }

    /**
     * Lancer un appel unique
     */
    public function call(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name'  => 'nullable|string',
        ]);

        $contact = Contact::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => $request->name, 'status' => 'pending']
        );

        $result = $this->telnyx->initiateCall($contact);

        if ($result['success']) {
            return back()->with('success', "Appel lancé vers {$contact->phone} (ID: {$result['call_sid']})");
        }

        return back()->with('error', "Échec : {$result['error']}");
    }

    /**
     * Lancer le prochain appel de la liste
     */
    public function callNext()
    {
        if (Contact::where('status', 'calling')->exists()) {
            return back()->with('warning', 'Un appel est déjà en cours. Attendez qu\'il se termine.');
        }

        $next = Contact::where('status', 'pending')->orderBy('id')->first();

        if (!$next) {
            return back()->with('info', 'Aucun contact en attente.');
        }

        $result = $this->telnyx->initiateCall($next);

        if ($result['success']) {
            return back()->with(
                'success',
                'Appel lancé vers ' . ($next?->name ?? $next?->phone ?? 'Inconnu')
            );
        }

        return back()->with('error', "Échec : {$result['error']}");
    }

    /**
     * Import depuis Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $ext  = $file->getClientOriginalExtension();
        $imported = 0;

        if ($ext === 'csv') {
            $rows = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_map('strtolower', array_shift($rows));
            foreach ($rows as $row) {
                $data = array_combine($header, $row);
                $phone = $data['phone'] ?? $data['telephone'] ?? $data['numero'] ?? $row[0] ?? null;
                if ($phone) {
                    Contact::firstOrCreate(['phone' => trim($phone)], [
                        'name'    => $data['name'] ?? $data['nom'] ?? null,
                        'company' => $data['company'] ?? $data['entreprise'] ?? null,
                        'status'  => 'pending',
                    ]);
                    $imported++;
                }
            }
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $header = array_map('strtolower', array_shift($sheet));
            foreach ($sheet as $row) {
                $data = array_combine($header, $row);
                $phone = $data['phone'] ?? $data['telephone'] ?? $data['numero'] ?? $row[0] ?? null;
                if ($phone) {
                    Contact::firstOrCreate(['phone' => trim($phone)], [
                        'name'    => $data['name'] ?? $data['nom'] ?? null,
                        'company' => $data['company'] ?? $data['entreprise'] ?? null,
                        'status'  => 'pending',
                    ]);
                    $imported++;
                }
            }
        }

        return back()->with('success', "$imported contacts importés avec succès.");
    }

    public function logs()
    {
        $logs = CallLog::with('contact')->latest()->paginate(20);
        return view('calls.logs', compact('logs'));
    }

    public function contacts()
    {
        $contacts = Contact::with('callLogs')->latest()->paginate(20);
        return view('contacts.index', compact('contacts'));
    }

    public function resetContact(Contact $contact)
    {
        $contact->update(['status' => 'pending']);
        return back()->with('success', 'Contact remis en attente.');
    }

    public function deleteContact(Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contact supprimé.');
    }

    public function prompts()
    {
        $prompts = OfferPrompt::all();
        return view('calls.prompts', compact('prompts'));
    }

    public function savePrompt(Request $request)
    {
        $request->validate([
            'name'            => 'required|string',
            'system_prompt'   => 'required|string',
            'opening_message' => 'required|string',
        ]);

        if ($request->has('id') && $request->id) {
            $prompt = OfferPrompt::findOrFail($request->id);
            $prompt->update($request->only('name', 'system_prompt', 'opening_message'));
        } else {
            OfferPrompt::create($request->only('name', 'system_prompt', 'opening_message'));
        }

        return back()->with('success', 'Prompt sauvegardé.');
    }

    public function activatePrompt(OfferPrompt $prompt)
    {
        OfferPrompt::where('id', '!=', $prompt->id)->update(['is_active' => false]);
        $prompt->update(['is_active' => true]);
        return back()->with('success', "Prompt « {$prompt->name} » activé.");
    }

    public function callStatus()
    {
        $calling = Contact::where('status', 'calling')->with(['callLogs' => fn($q) => $q->latest()->limit(1)])->first();
        return response()->json([
            'calling'     => $calling ? $calling->only('id', 'name', 'phone') : null,
            'pending'     => Contact::where('status', 'pending')->count(),
            'lastResult'  => CallLog::latest()->first()?->only('result', 'duration', 'notes'),
        ]);
    }
}