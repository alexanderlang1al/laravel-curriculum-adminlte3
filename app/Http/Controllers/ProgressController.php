<?php

namespace App\Http\Controllers;

use App\Achievement;
use App\EnablingObjective;
use App\Progress;
use App\TerminalObjective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        switch ($request->referencable_type) {
            case 'App\TerminalObjective':
                $input = $this->validateRequest();

                $model = TerminalObjective::find($input['parent_id']);
                abort_unless($model->isAccessible(), 403);

                $enabling_objectives = EnablingObjective::where('terminal_objective_id', $input['parent_id'])->get();

                $total_achieved = Achievement::where('referenceable_type', 'App\\EnablingObjective')
                    ->where('user_id', $user_id)
                    ->whereIn('referenceable_id', $enabling_objectives->pluck('id'))
                    ->where(DB::raw('RIGHT(status,1) = 1 OR RIGHT(status,1) = 2'))
                    ->get();
                $progress = Progress::updateOrCreate(
                    [
                        'referenceable_type' => $input['referenceable_type'],
                        'referenceable_id' => $input['parent_id'],
                        'associable_type' => 'App\\User',
                        'associable_id' => $user_id,
                    ],
                    [
                        'value' => ($total_achieved->count() / $enabling_objectives->count() * 100),
                    ]
                );
                break;

            default:
                break;
        }
    }

    protected function validateRequest()
    {
        return request()->validate([
            'referenceable_type'    => 'required',
            'referenceable_id'      => 'required',
            'associable_type'       => 'required',
            'associable_id'         => 'required',
            'value'                 => 'required',
        ]);
    }

    /**
     * Example (new ProgressController)->calculateProgress('App\TerminalObjective', $terminal_objective_id, $user_id);
     *
     * @param  type  $parent_model
     * @param  type  $parent_id
     * @param  type  $user_id
     * @return type
     */
    public function calculateProgress($parent_model, $parent_id, $user_id)
    {
        $dynamicFunction = 'calculate'.class_basename($parent_model).'Progress';

        return $this->$dynamicFunction($parent_model, $parent_id, $user_id);
    }

    /**
     * calculate users terminal objective progress based on enabling objectives achievements
     *
     * @param  type  $parent_model
     * @param  type  $parent_id
     * @param  type  $user_id
     * @return type
     */
    public function calculateTerminalObjectiveProgress($parent_model, $parent_id, $user_id)
    {
        //Parent App\TerminalObjective
        $enabling_objectives = EnablingObjective::where('terminal_objective_id', $parent_id)->get();

        $total_achieved = Achievement::where('referenceable_type', 'App\\EnablingObjective')
                                ->where('user_id', $user_id)
                                ->whereIn('referenceable_id', $enabling_objectives->pluck('id'))
                                ->whereRaw('(RIGHT(status,1) = "1" OR RIGHT(status,1) = "2")')
                                ->get();
        $progress = Progress::updateOrCreate(
            [
                'referenceable_type' => $parent_model,
                'referenceable_id' => $parent_id,
                'associable_type' => 'App\\User',
                'associable_id' => $user_id,
            ],
            [
                'value' => ($total_achieved->count() / $enabling_objectives->count() * 100),
            ]

        );

        $this->calculateProgress('App\Curriculum', $enabling_objectives->first()->curriculum_id, $user_id);

        return $progress;
    }

    public function calculateCurriculumProgress($parent_model, $parent_id, $user_id)
    {
        $terminal_objectives = TerminalObjective::where('curriculum_id', $parent_id)->get();
        $terminal_objective_progresses = Progress::where('referenceable_type', 'App\\TerminalObjective')
                ->where('associable_id', $user_id)
                ->whereIn('referenceable_id', $terminal_objectives->pluck('id'))
                ->get();

        return Progress::updateOrCreate(
            [
                'referenceable_type' => $parent_model,
                'referenceable_id' => $parent_id,
                'associable_type' => 'App\\User',
                'associable_id' => $user_id,
            ],
            [
                'value' => floor($terminal_objective_progresses->sum('value') / $terminal_objectives->count()),
            ]
        );
    }
}
