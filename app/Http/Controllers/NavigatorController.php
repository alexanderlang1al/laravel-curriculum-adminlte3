<?php

namespace App\Http\Controllers;

use App\Navigator;
use App\NavigatorView;
use App\Organization;
use File;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class NavigatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('navigator_access'), 403);

        $navigators = Navigator::all();

        return view('navigators.index')
          ->with(compact('navigators'));
    }
    
    public function list()
    {
        
        abort_unless(\Gate::allows('navigator_access'), 403);
        $navigators = Navigator::select([
            'id', 
            'title', 
            'organization_id'
            ]);
        
        return DataTables::of($navigators)
            ->addColumn('organization', function ($navigators) {
                return $navigators->organization()->first()->title;                
            })
            ->addColumn('action', function ($navigators) {
                 $actions  = '';
                    if (\Gate::allows('navigator_show')){
                        $actions .= '<a href="'.route('navigators.show', $navigators->id).'" '
                                    . 'class="btn btn-xs btn-success">'
                                    . '<i class="fa fa-list-alt"></i> Show'
                                    . '</a>';
                    }
                    if (\Gate::allows('navigator_edit')){
                        $actions .= '<a href="'.route('navigators.edit', $navigators->id).'" '
                                    . 'class="btn btn-xs btn-primary mr-1">'
                                    . '<i class="fa fa-edit"></i> '.trans('global.edit').''
                                    . '</a>';
                    }
                    if (\Gate::allows('navigator_delete')){
                        $actions .= '<form action="'.route('navigators.destroy', $navigators->id).'" method="POST">'
                                    . '<input type="hidden" name="_method" value="delete">'. csrf_field().''
                                    . '<button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</button>';
                    }
              
                return $actions;
            })
           
            ->addColumn('check', '')
            ->setRowId('id')
            ->setRowAttr([
                'color' => 'primary',
            ])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         abort_unless(\Gate::allows('navigator_create'), 403);
        
        $organizations = Organization::all();
        return view('navigators.create')
                ->with(compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('navigator_create'), 403);
        $new_navigator = $this->validateRequest();
        
        $navigator = Navigator::firstOrCreate([
            'title' => $new_navigator['title'],
            'organization_id' => format_select_input($new_navigator['organization_id'])
        ]);
        
        // axios call? 
        if (request()->wantsJson()){    
            return ['message' => $navigator->path()];
        }
        
        return redirect($navigator->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Navigator  $navigator
     * @return \Illuminate\Http\Response
     */
    public function show(Navigator $navigator)
    {
        $navigators = Navigator::where('id', $navigator->id)->get()->first();
        $views      = NavigatorView::where('navigator_id', $navigator->id)
                                    ->with(['items'])
                                    ->get()->first();
                                       
        return view('navigators.show')
                ->with(compact('navigators'))
                ->with(compact('views'));
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Navigator  $navigator
     * @return \Illuminate\Http\Response
     */
    public function edit(Navigator $navigator)
    {
        abort_unless(\Gate::allows('navigator_edit'), 403);
        $organizations = Organization::all();
        
        return view('navigators.edit')
                ->with(compact('navigator'))
                ->with(compact('organizations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Navigator  $navigator
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Navigator $navigator)
    {
        abort_unless(\Gate::allows('navigator_edit'), 403);
        
        $navigator->update([
            'title' => $request['title'],
            'organization_id' => format_select_input($request['organization_id']),
        ]);

        return redirect()->route('navigators.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Navigator  $navigator
     * @return \Illuminate\Http\Response
     */
    public function destroy(Navigator $navigator)
    {
        abort_unless(\Gate::allows('navigator_delete'), 403);

        $navigator->delete();

        return back();
    }
    
    protected function validateRequest()
    {   
        
        return request()->validate([
            'title'             => 'sometimes|required',
            'organization_id'   => 'sometimes',
        ]);
    }
}