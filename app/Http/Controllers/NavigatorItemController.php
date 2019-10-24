<?php

namespace App\Http\Controllers;

use App\Content;
use App\Curriculum;
use App\NavigatorItem;
use App\NavigatorView;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Medium;


class NavigatorItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $referenceable_types = [
            (object) ['class' => 'App\NavigatorView', 'label' => trans('global.referenceable_types.navigator_view')], 
            (object) ['class' => 'App\Curriculum', 'label' => trans('global.referenceable_types.curriculum')], 
            (object) ['class' => 'App\Content', 'label' => trans('global.referenceable_types.content')], 
            (object) ['class' => 'App\Medium', 'label' => trans('global.referenceable_types.medium')], 
        ];
        $position = [
            (object) ['id' => 'content', 'label' => trans('global.content')], 
            (object) ['id' => 'footer', 'label' => trans('global.footer')], 
            (object) ['id' => 'header', 'label' => trans('global.header')], 
        ];
        $css_classes = [
            (object) ['class' => 'col-xs-12', 'label' => 'col-xs-12'], 
            (object) ['class' => 'col-12', 'label' => 'col-12'], 
        ];
        $visibility = [
            (object) ['id' => '1', 'label' => trans('global.navigator_item.fields.visibility_show')], 
            (object) ['id' => '0', 'label' => trans('global.navigator_item.fields.visibility_hide')], 
            
        ];
        
        $curricula = Curriculum::all();
        $media = Medium::where('path', '/subjects/')->get(); //todo: only show usable media (e.g. images)
        $navigator_id = request()->navigator_id;
        $view_id = request()->view_id;
        
        return view('navigators.views.items.create')
                    ->with(compact('navigator_id'))
                    ->with(compact('view_id'))
                    ->with(compact('referenceable_types'))
                    ->with(compact('position'))
                    ->with(compact('css_classes'))
                    ->with(compact('visibility'))
                    ->with(compact('curricula'))
                    ->with(compact('media'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('navigator_create'), 403);
        $new_navigator_item = $this->validateRequest();
       
        $navigator_id = request()->navigator_id;
        $view_id = request()->view_id;
        
        switch (format_select_input($new_navigator_item['referenceable_type'])) {
            case 'App\Content':         $content = new Content([
                                                                'title' => $new_navigator_item['title'],
                                                                'content' => $new_navigator_item['description'],
                                                                'owner_id' => auth()->user()->id
                                                             ]);
                                        $content->save();
                                        $title            = $new_navigator_item['title'];
                                        $description      = str_limit($new_navigator_item['description'], 200);
                                        $referenceable_id = $content->id;
                break;
            
            case 'App\Curriculum':      $referenceable_id = format_select_input($new_navigator_item['referenceable_id']);
                                        
                                        $curriculum = Curriculum::find($referenceable_id);
                                        $title            = $curriculum->title;
                                        $description      = $curriculum->description;                  
                break;
            case 'App\NavigatorView':   $navigator_view = new NavigatorView([
                                                                'title' => $new_navigator_item['title'],
                                                                'description' => $new_navigator_item['description'],
                                                                'navigator_id' => $navigator_id
                                                             ]);
                                        $navigator_view->save();
                                        
                                        $title            = $new_navigator_item['title'];
                                        $description      = $new_navigator_item['description'];
                                        $referenceable_id = $navigator_view->id;
                break;
            case 'App\Medium':          $title            = $new_navigator_item['title'];
                                        $description      = $new_navigator_item['description'];
                                        $referenceable_id = format_select_input($new_navigator_item['medium_id']);
                break;
            

            default:
                break;
        }
        
        $navigator_item = NavigatorItem::firstOrCreate([
            'title'              => $title,
            'description'        => $description,
            'navigator_view_id'  => $view_id,
            'referenceable_type' => format_select_input($new_navigator_item['referenceable_type']),
            'referenceable_id'   => $referenceable_id,
            'position'           => format_select_input($new_navigator_item['position']),
            'css_class'          => format_select_input($new_navigator_item['css_class']),
            'visibility'         => format_select_input($new_navigator_item['visibility'])
        ]);
        
        /* subscribe image */
        if (isset($new_navigator_item['medium_id']))
        {
            $medium = Medium::find(format_select_input($new_navigator_item['medium_id'])); 
            $medium->subscribe($navigator_item);
        }
        
        
        // axios call? 
        if (request()->wantsJson()){    
            return ['message' => $navigator_item->path()];
        }
        
        return redirect()->route("navigator.view", ['navigator' => $navigator_id, 'navigator_view' => $view_id]);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\NavigatorItem  $navigatorItem
     * @return Response
     */
    public function edit(NavigatorItem $navigatorItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  \App\NavigatorItem  $navigatorItem
     * @return Response
     */
    public function update(Request $request, NavigatorItem $navigatorItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\NavigatorItem  $navigatorItem
     * @return Response
     */
    public function destroy(NavigatorItem $navigatorItem)
    {
        abort_unless(\Gate::allows('navigator_delete'), 403);
        $navigatorItem->delete();

        return back();
    }
    
    protected function validateRequest()
    {   
        
        return request()->validate([
            'title'             => 'sometimes',
            'description'       => 'sometimes',
            'referenceable_type'=> 'sometimes',
            'referenceable_id'  => 'sometimes',
            'medium_id'         => 'sometimes',
            'position'          => 'sometimes',
            'css_class'         => 'sometimes',
            'visibility'        => 'sometimes',
        ]);
    }
}