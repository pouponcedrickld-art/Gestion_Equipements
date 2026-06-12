<?php

namespace App\Providers;

use App\Events\PanneDeclaree;
use App\Events\PanneCloturee;
use App\Events\PanneDecisionIrrecuperable;
use App\Events\PanneDecisionReparee;
use App\Events\PanneDecisionRemplacementNecessaire;
use App\Events\PanneDiagnosticEnregistre;
use App\Listeners\PanneTechnicienWorkflowListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PanneDeclaree::class => [
            [PanneTechnicienWorkflowListener::class, 'handlePanneDeclaree'],
        ],
        PanneDiagnosticEnregistre::class => [
            [PanneTechnicienWorkflowListener::class, 'handlePanneDiagnosticEnregistre'],
        ],
        PanneDecisionReparee::class => [
            [PanneTechnicienWorkflowListener::class, 'handlePanneDecisionReparee'],
        ],
        PanneDecisionRemplacementNecessaire::class => [
            [PanneTechnicienWorkflowListener::class, 'handlePanneDecisionRemplacementNecessaire'],
        ],
        PanneDecisionIrrecuperable::class => [
            [PanneTechnicienWorkflowListener::class, 'handlePanneDecisionIrrecuperable'],
        ],
        PanneCloturee::class => [
            [PanneTechnicienWorkflowListener::class, 'handlePanneCloturee'],
        ],
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
