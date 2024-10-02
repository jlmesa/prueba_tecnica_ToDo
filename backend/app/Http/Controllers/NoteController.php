<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Note::where('user_id', Auth::id());

        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            if (in_array($sortBy, ['created_at', 'fecha_vencimiento'])) {
                $query->orderBy($sortBy);
            }
        }

        return response()->json($query->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'etiquetas' => 'required|string',
            'fecha_vencimiento' => 'nullable|date',
            'imagen' => 'nullable|image',
        ]);

        if ($request->hasFile('imagen')) {
            $imagePath = $request->file('imagen')->store('images', 'public');
        } else {
            $imagePath = null;
        }

        $note = Note::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'user_id' => Auth::id(),
            'etiquetas' => $request->etiquetas,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'imagen' => $imagePath ? asset('storage/' . $imagePath) : null,
        ]);

        return response()->json($note, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        return response()->json($note);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        try {
           $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string',
            'etiquetas' => 'sometimes|required|string',
            'fecha_vencimiento' => 'nullable|date',
            'imagen' => 'nullable|image',
        ]);

        $note->titulo = $request->titulo;
        $note->descripcion = $request->descripcion;
        $note->etiquetas = $request->etiquetas;
        $note->fecha_vencimiento = $request->fecha_vencimiento;

        if ($request->hasFile('imagen')) {
            $note->imagen = asset('storage/' . $request->file('imagen')->store('images', 'public'));
        }

        $note->save();

        return response()->json($note); 
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        //$this->authorize('delete', $note);
        
        $note->delete();

        return response()->json(null, 204);
    }
}
