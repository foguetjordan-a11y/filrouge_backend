<?php

namespace App\Http\Controllers;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Admin ajoute une note
     */
    public function store(Request $request)
    {
        $request->validate([
            'enrollement_id' => 'required|exists:enrollements,id',
            'matiere_id' => 'required|exists:matieres,id',
            'note' => 'required|numeric|min:0|max:20',
        ]);

        $note = Note::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Note enregistrée avec succès',
            'data' => $note
        ], 201);
    }

    /**
     * Étudiant consulte ses notes
     */
    public function mesNotes(Request $request)
    {
        $notes = Note::whereHas('enrollement', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('matiere')->get();

        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }
    public function moyenne(Request $request)
    {
        $notes = $this->mesNotes($request);
        return $notes->avg('note');
    }
    public function decision(Request $request)
    {
        return $this->moyenne($request) >= 10 ? 'ADMIS' : 'AJOURNE';
    }
}

