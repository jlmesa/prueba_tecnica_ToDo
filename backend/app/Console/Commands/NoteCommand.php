<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;

class NoteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'note:create {titulo} {descripcion} {--etiquetas} {--fecha_vencimiento=} {--imagen=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear Nota Nueva';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $note = Note::create([
            'titulo' => $this->argument('titulo'),
            'descripcion' => $this->argument('descripcion'),
            'user_id' => Auth::id(),
            'etiquetas' => $this->argument('etiquetas'),
            'fecha_vencimiento' => $this->option('fecha_vencimiento'),
            'imagen' => $this->option('imagen'),
        ]);

        $this->info("Nota creada: {$note->titulo}");
    }
}
