<?php

namespace App\Http\Controllers;

use App\Certificate;
use App\Curriculum;
use App\Medium;
use App\Organization;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('certificate_access'), 403);

        return view('certificates.index');
    }

    public function list()
    {
        abort_unless(\Gate::allows('certificate_access'), 403);
        $certificates = Certificate::select([
            'id',
            'title',
            'description',
            'body',
            'curriculum_id',
            'organization_id',
            'owner_id',
        ])->with(['organization', 'curriculum', 'owner'])->where('owner_id', auth()->user()->id);

        return DataTables::of($certificates)
            ->addColumn('organization', function ($certificates) {
                return $certificates->organization->title;
            })
            ->addColumn('curriculum', function ($certificates) {
                return $certificates->curriculum->title;
            })
            ->addColumn('owner', function ($certificates) {
                return $certificates->owner->firstname.' '.$certificates->owner->lastname;
            })
            ->addColumn('action', function ($certificates) {
                $actions = '';
                if (\Gate::allows('certificate_show')) {
                    $actions .= '<a href="'.route('certificates.show', $certificates->id).'" '
                                    .'class="btn">'
                                    .'<i class="fa fa-list-alt"></i>'
                                    .'</a>';
                }
                if (\Gate::allows('certificate_edit')) {
                    $actions .= '<a href="'.route('certificates.edit', $certificates->id).'" '
                                    .'class="btn">'
                                    .'<i class="fa fa-pencil-alt"></i>'
                                    .'</a>';
                }
                if (\Gate::allows('certificate_delete')) {
                    $actions .= '<button type="button" class="btn text-danger" onclick="destroyCertificate('.$certificates->id.')"><i class="fa fa-trash"></i></button>';
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
    public function create(Request $request)
    {
        abort_unless(\Gate::allows('certificate_create'), 403);

        $curricula = Curriculum::where('id', $request->query('curriculum_id'))->get();
        $organisations = auth()->user()->organizations()->get();

        $certificate = new Certificate();
        $certificate->curriculum_id = $request->query('curriculum_id');
        $certificate->organization_id = auth()->user()->current_organization_id;

        return view('certificates.create')
                ->with(compact('curricula'))
                ->with(compact('certificate'))
                ->with(compact('organisations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('certificate_create'), 403);
        $input = $this->validateRequest();
        //dd($input);
        $certificate = Certificate::firstOrCreate([
            'title'           => $input['title'],
            'description'     => $input['description'],
            'body'            => $input['body'],
            'curriculum_id'   => format_select_input($input['curriculum_id']),
            'organization_id' => format_select_input($input['organization_id']),
            'owner_id'        => auth()->user()->id,
            'global'          => isset($input['global']) ? 1 : '0',
        ]);

        // axios call?
        if (request()->wantsJson()) {
            return ['message' => $certificate->path()];
        }

        return redirect($certificate->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function show(Certificate $certificate)
    {
        return view('certificates.show')
         ->with(compact('certificate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function edit(Certificate $certificate)
    {
        abort_unless(\Gate::allows('certificate_edit'), 403);
        $curricula = Curriculum::where('owner_id', auth()->user()->id)->get();
        $organisations = auth()->user()->organizations()->get();

        return view('certificates.edit')
            ->with(compact('certificate'))
            ->with(compact('curricula'))
            ->with(compact('organisations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Certificate $certificate)
    {
        abort_unless(\Gate::allows('certificate_edit'), 403);

        $input = $this->validateRequest();

        $certificate->update([
            'title' => $input['title'],
            'description' => $input['description'],
            'body' => $input['body'],
            'curriculum_id' => format_select_input($input['curriculum_id']) ?? $certificate->curriculum_id,
            'organization_id' => format_select_input($input['organization_id']) ?? $certificate->organization_id,
            'global' => isset($input['global']) ? 1 : '0',
        ]);

        return redirect()->route('certificates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Certificate $certificate)
    {
        abort_unless(\Gate::allows('certificate_delete'), 403);

        $certificate->delete();

        return back();
    }

    /**
     * Generate a certificate based on existing template
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        abort_unless(\Gate::allows('certificate_access'), 403);
        $user_ids = explode(',', request()->user_ids);
        $certificate = Certificate::find(request()->certificate_id);

        switch ($certificate->type) {
            case 'user':            LogController::set(get_class($this).'@'.__FUNCTION__, request()->certificate_id, (is_array($user_ids)) ? count($user_ids) : 1);
                                    return $this->generateForUsers($certificate);
                break;
            case 'group':           return $this->generateForGroup($certificate);
                break;
            case 'organization':    return $this->generateForOrganization($certificate);
                break;

            default:
                break;
        }
    }

    /**
     * Generate certificate(s) for user(s)
     *
     * @param  object  $certificate
     * @return string medium->path()
     */
    protected function generateForUsers($certificate)
    {
        $user_ids = explode(',', request()->user_ids);
        $generated_files = [];
        if(str_contains($certificate->body, '[accomplished_objectives]') || str_contains($certificate->body, '[accomplished_objectives_without_terminal_objectives]') )
        {
            $curriculum = Curriculum::with([
                'terminalObjectives',
                'terminalObjectives.enablingObjectives',])
                ->find((request()->curriculum_id != null) ? request()->curriculum_id : $certificate->curriculum_id);
        }

        foreach ($user_ids as $id) {
            $user = User::where('id', $id)->get()->first();
            abort_unless(auth()->user()->mayAccessUser($user), 403);
            //replace placeholder
            $html = $this->replaceFields(
                    $certificate->body,
                    $user,
                    Organization::where('id', auth()->user()->current_organization_id)->get()->first(),
                    request()->date);

            if(str_contains($certificate->body, '[accomplished_objectives]'))
            {
                $html = $this->generateAccomplishedObjectiveList($html, $id, $curriculum); //generate list if "[accomplished_objectives]" is in certificate
            }
            if(str_contains($certificate->body, '[accomplished_objectives_without_terminal_objectives]'))
            {
                $html = $this->generateAccomplishedObjectiveList($html, $id, $curriculum, '[accomplished_objectives_without_terminal_objectives]', false); //generate list if "[accomplished_objectives]" is in certificate
            }


            $html = preg_replace_callback(
                '/<span\s+[^>]*reference_type="(.*?)"\s+[^>]*reference_id="(.*?)"\s+[^>]*min_value="(.*?)"[^>]*>(.*?)<\/span>/mis',
                function ($match) use ($user) {
                    // evaluate progress
                    // Example
                    // Full match <span reference_type="App\TerminalObjective" reference_id="1" min_value="60"/><img src="/media/2"/></span>
                    // Group 1 | $match[1] App\TerminalObjective
                    // Group 2 | $match[2] 1
                    // Group 3 | $match[3] 60
                    // Group 4 | $match[4] <img src="/media/2"/>

                    $associable_type = 'App\User';
                    $associable_id = $user->id;

                    // foreach(explode(",", $match[2]) as $terid) // recalc progress -> only for dev
                    // {
                    //      (new ProgressController)->calculateProgress('App\TerminalObjective', $terid, $associable_id);
                    // }

                    $progress = \App\Progress::where('referenceable_type', $match[1])
                                ->whereIn('referenceable_id', explode(',', $match[2]))
                                ->where('associable_type', $associable_type)
                                ->where('associable_id', $associable_id)
                                ->get();
                    //dump($progress->avg('value').'_'.$progress->avg('value').'_'.$match[3]);
                    return ($progress->avg('value') != null and $progress->avg('value') >= (int) $match[3])
                            ? $match[4]
                            : '<div style="opacity: .5;">'.$match[4].'</div>';
                },

                $html
            );
            //end progress
            $filename = date('Y-m-d_H-i-s').str_replace_special_chars($user->lastname.'_'.$user->firstname).'.pdf'; //Username escape german umlaute
            $path = 'users/'.auth()->user()->id.'/';


            if(!Storage::exists($path)){
                Storage::makeDirectory($path);
            }

            $pathOfNewFile = $this->buildPdf($html, $path, $filename);

            array_push($generated_files, ['filename' => $filename, 'path' => Storage::disk('local')->path($path.$filename)]);
        }//end foreach

        if (request()->wantsJson()) {
            if (count($generated_files) > 1) { //zip if more than one files
                return ['message' => $this->zipper($path, $generated_files)];
            } else { //only one file ? return pdf
                return ['message' => $pathOfNewFile];
            }
        }
    }


    protected function generateAccomplishedObjectiveList($html, $user_id, $curriculum, $replace = "[accomplished_objectives]", $with_terminal_objectives = true)
    {
        $accomplished_list = '';
        foreach ($curriculum->terminalObjectives as $ter_value) {
            $i = 0;
            foreach ($ter_value->enablingObjectives as $ena) {

               $status = optional(\App\Achievement::where(
                    [
                        'referenceable_type' => 'App\EnablingObjective',
                        'referenceable_id'   => $ena->id,
                        'user_id'            => $user_id,
                    ])->get()->first())->status;
                if (in_array($status, ['01', '11', '21', '31'] ) OR in_array($status, ['02', '12', '22', '32']))
                {
                    if ($i === 0) //
                    {
                        if ($with_terminal_objectives)
                        {
                            $accomplished_list .= '<strong>'.strip_tags($ter_value->title).'</strong><br>';
                        }

                        $i++; //iterator for terminal objective output
                    }
                    if (in_array($status, ['01', '11', '21', '31']))
                    {
                        $accomplished_list .= strip_tags($ena->title).'<br>';
                    } else {
                        $accomplished_list .= '('.strip_tags($ena->title).')<br>';
                    }
                }
            }
        }
        return str_replace($replace, $accomplished_list,$html);
    }

    /**
     * Generate certificate for group
     *
     * @param  object  $certificate
     */
    protected function generateForGroup($certificate)
    {
        $user_ids = explode(',', request()->user_ids);

        $td_style = 'style="border-bottom: 1px solid silver;border-right: 1px solid silver;"';

        $html = '<table repeat_header="1" style="width: 100%;padding-bottom: 10px;" border="0"><tbody>'
                .'<thead><tr><td style="border-bottom: 1px solid silver;"><strong>Ziele / Namen</strong></td>';
        foreach ($user_ids as $id) {

            abort_unless(auth()->user()->mayAccessUser(User::find($id)), 403);
            $user = User::where('id', $id)->get()->first();
            $html .= '<td '.$td_style.'><strong>'.$user->firstname.' '.$user->lastname.'</strong></td>';
        }
        $html .= '</tr></thead>';

        $curriculum = Curriculum::with([
            'terminalObjectives',
            'terminalObjectives.enablingObjectives', ])
            ->find((request()->curriculum_id != null) ? request()->curriculum_id : $certificate->curriculum_id);

        foreach ($curriculum->terminalObjectives as $ter_value) {
            $html .= '<tr><td '.$td_style.' colspan="'.( count($user_ids) + 1 ).'"><strong>'.strip_tags($ter_value->title).'</strong></td></tr>';

            foreach ($ter_value->enablingObjectives as $ena) {
                $html .= '<tr><td style="width: 25%;border-bottom: 1px solid silver;border-right: 1px solid silver;">'.strip_tags($ena->title).'</td>';
                foreach ($user_ids as $user_id) {
                    $html .= '<td style="text-align: center; border-bottom: 1px solid silver;border-right: 1px solid silver;">';
                    $html .= $this->achievementIndicator(optional(\App\Achievement::where(
                            [
                                'referenceable_type' => 'App\EnablingObjective',
                                'referenceable_id'   => $ena->id,
                                'user_id'            => $user_id,
                            ])->get()->first())->status);

                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
        }

        //$timestamp = date("Y-m-d_H-i-s");
        $html .= '</tbody></table>';
        $filename = date('Y-m-d_H-i-s').str_replace_special_chars($user->lastname.'_'.$user->firstname).'.pdf'; //Username escape german umlaute
        //$filename = $timestamp.$user->lastname."_".$user->firstname.".pdf";
        $path = 'users/'.auth()->user()->id.'/';

        if(!Storage::exists($path)){
            Storage::makeDirectory($path);
        }

        if (request()->wantsJson()) {
            return ['message' => $this->buildPdf($html, $path, $filename, 'landscape')];
        }
    }

    protected function replaceFields($string, $user, $organization, $date)
    {
        //enhance replacement
        $search = [
            '/<span (.*)id="firstname"(.*)>(.*)<\/span>/Umi',
            '/<span (.*)id="lastname"(.*)>(.*)<\/span>/Umi',
            '/<span (.*)id="organization_title"(.*)>(.*)<\/span>/Umi',
            '/<span (.*)id="organization_street"(.*)>(.*)<\/span>/Umi',
            '/<span (.*)id="organization_postcode"(.*)>(.*)<\/span>/Umi',
            '/<span (.*)id="organization_city"(.*)>(.*)<\/span>/Umi',
            '/<span (.*)id="date"(.*)>(.*)<\/span>/Umi',
        ];
        $replace = [
            $user->firstname,
            $user->lastname,
            $organization->title,
            $organization->street,
            $organization->postcode,
            $organization->city,
            $date, ];

        return preg_replace($search, $replace, $string);
    }

    protected function achievementIndicator($status)
    {
        $span_style = 'style="text-align: center; font-family: DejaVu Sans;"';

        switch (true) {
            case in_array($status, ['01', '11', '21', '31']): $html = '<span '.$span_style.'>&#10004;</span>';
                break;
            case in_array($status, ['02', '12', '22', '32']): $html = '<span '.$span_style.'>(&#10004;)</span>';
                break;
            case in_array($status, ['03', '13', '23', '33']): $html = '<span '.$span_style.'>&#10007;</span>';
                break;

            default:  $html = '<span '.$span_style.'></span>';
                break;
        }

        return $html;
    }

    /**
     * Generate certificate for organization
     *
     * @param  object  $certificate
     */
    protected function generateForOrganization($certificate)
    {
        //todo
    }

    protected function buildPdf($html, $path, $filename, $orientation = 'portrait')
    {
        /* replace relative media links with absolute paths to get snappy working */
        $html = relativeToAbsolutePaths($html);

        $pdf = Pdf::loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.$html)
            ->setPaper('a4')
            //->setOrientation($orientation)
            ->setOption('margin-bottom', 0)
            ->setOption('enable-local-file-access', true)
            ->save(storage_path('app/'.$path.$filename));
        return $this->addFileToDb($filename);

        // replaced
  /*      SnappyPdf::loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.$html)
                ->setPaper('a4')
                ->setOrientation($orientation)
                ->setOption('margin-bottom', 0)
                ->setOption('enable-local-file-access', true)
                ->save(storage_path('app/'.$path.$filename));

        return $this->addFileToDb($filename);*/
    }

    protected function zipper($path, $files)
    {
        $filename = date('Y-m-d_H-i-s').'.zip';
        $zip_file = storage_path('app/'.$path.$filename);
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['filename']);
        }

        $zip->close();

        return $this->addFileToDb($filename); //returns $media->path()
    }

    protected function addFileToDb($filename)
    {
        $media = new Medium([
            'path'          => '/users/'.auth()->user()->id.'/',
            'title'         => $filename,
            'medium_name'   => $filename,
            'description'   => '',
            'author'        => auth()->user()->fullName(),
            'publisher'     => '',
            'city'          => '',
            'date'          => date('Y-m-d_H-i-s'),
            'size'          => File::size(Storage::disk('local')->path('users/'.auth()->user()->id.'/'.$filename)),
            'mime_type'     => File::mimeType(Storage::disk('local')->path('users/'.auth()->user()->id.'/'.$filename)),
            'license_id'    => 2,

            'public'        => 0,           //certificates can not be accessed without owership/subscription
            'owner_id'      => auth()->user()->id,
        ]);
        $media->save();

        /*
         * add subscription
         */
        $media->subscribe(auth()->user(), 4);

        return  $media->path();
    }

    protected function validateRequest()
    {
        return request()->validate([
            'title'                 => 'sometimes|required',
            'description'           => 'sometimes',
            'body'                  => 'sometimes',
            'curriculum_id'         => 'sometimes',
            'organization_id'       => 'sometimes',
            'global'                => 'sometimes',
        ]);
    }
}
