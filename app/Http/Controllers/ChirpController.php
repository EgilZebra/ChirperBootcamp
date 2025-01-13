<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use OpenAI\Laravel\Facades\OpenAI;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        $alternatives = session('alternatives');

        return Inertia::render('Chirps/Index', [
            'chirps' => Chirp::with('user:id,name')->latest()->get(),
            'alternatives' => $alternatives,
        ]);
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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $request->user()->chirps()->create($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        Gate::authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    public function destroy(Chirp $chirp): RedirectResponse
    {
        Gate::authorize('delete', $chirp);
        $chirp->delete();
        return redirect(route('chirps.index'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'mood' => 'required|string|max:255',
            'seriousnessLevel' => 'required|integer|min:1|max:3',
        ]);

        $alternatives = [];

        for ($i = 0; $i < 3; $i++ ){
            $result = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Write me a social media post, no more than 180 characters.
                            On the topic of {$validated['topic']}.
                            Based on the {$validated['mood']} feeling.
                            And on a seriousness level of {$validated['seriousnessLevel']} in a 3-grade scale.",
                    ],
                ],
            ]);
 
            $generatedResponse = $result->choices[0]->message->content;
            $alternatives[] = $generatedResponse;
        }

        session()->flash('alternatives', $alternatives);

        return redirect(route('chirps.index'));
    }
}
