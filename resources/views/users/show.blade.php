@extends('layouts.master')
@section('title')
    {{ trans('global.myProfile') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item active">{{ trans('global.myProfile') }}</li>
    <li class="breadcrumb-item "><a href="/documentation" class="text-black-50"><i class="fas fa-question-circle"></i></a></li>
@endsection
@section('content')
<div class="row">
    <div class="col-lg-3 col-sm-12">
        <div class="card card-primary card-outline">
        <div class="card-body box-profile">
            <div class="text-center">
                <img id="avatarThumb"
                     name="avatarThumb"
                     class="profile-user-img img-fluid img-circle"
                     style="height:100px;"
                     src="{{ ($user->medium_id !== null) ? '/media/'.$user->medium_id  : Avatar::create($user->fullName())->toBase64() }}"
                     alt="User profile picture"
                     onclick="app.__vue__.$modal.show('medium-create-modal',  {'target': 'medium_id', 'description': {{ json_encode('') }}, 'accept': 'image/*' });">
            </div>
            <input id="medium_id"
                   name="medium_id"
                   class="form-control"
                   type="hidden"
                   onchange="setAvatar()"
                   value="{{ $user->medium_id  }}">
                <h3 class="profile-username text-center">{{ $user->firstname }} {{ $user->lastname }}</h3>

                <p class="text-muted text-center">{{ $user->username }} ({{ (null !== $user->currentRole()->first()) ? $user->currentRole()->first()->title : '' }})</p>
            @can('user_edit')
                <a class="float-right link-muted" href="{{ route('users.edit', $user->id) }}" >
                    <i class="fa fa-pencil-alt"></i>
                </a>
            @endcan
              {{--  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Accomplished</b> <a class="float-right">1,322</a>
                    </li>
                </ul>--}}
            </div>
            <!-- /.card-body -->
        </div>

        <div class="card card-primary">

            <!-- /.card-header -->
            <div class="card-body">
                <strong><i class="fa fa-university mr-1"></i>{{ trans('global.organization.title_singular') }}</strong>
                    <ul class="pl-4">
                    @foreach($user->organizations as $id => $organizations)
                        <li class="small">{{$organizations->title}} @ {{ $user->roles()->where('organization_id', $organizations->id)->first()->title }}</li>
                    @endforeach
                    </ul>
                <hr>

                <strong><i class="fa fa-users mr-1"></i>{{ trans('global.group.title_singular') }}</strong>
                    <ul class="pl-4">
                    @foreach($user->groups as $id => $groups)
                        <li class="small">{{$groups->title}} @ {{ $groups->organization->title }}</li>
                    @endforeach
                    </ul>
                <hr>
                <strong><i class="fas fa-user-tag mr-1"></i>{{ trans('global.roles') }}</strong>
                    <ul class="pl-4">
                    @foreach($user->organizations as $id => $organizations)
                        <li class="small">{{ $user->roles()->where('organization_id', $organizations->id)->first()->title }} @ {{$organizations->title}}</li>
                    @endforeach
                    </ul>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
                {{--<div class="float-left">
                    <button type="button" class="btn-xs btn-block btn-{{$status_definitions[$user->status_id]->color_css_class}} pull-right">{{$status_definitions[$user->status_id]->lang_de}}</button>
                </div>--}}
                {{--@can('user_access')
                <div class="float-left">
                    <a class="btn-xs btn-success" href="/users/{{ $user->id }}/dsgvoExport" >
                        {{ trans('global.user.dsgvoExport') }}
                    </a>
                </div>
                @endcan--}}
                <small class="float-right">
                    {{ $user->updated_at }}
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-9 col-sm-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active show" href="#contact" data-toggle="tab">
                            {{ trans('global.contactdetail.title_singular') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link show" href="#notes" data-toggle="tab">
                            {{ trans('global.note.title') }}
                        </a>
                    </li>
                </ul>

            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active show" id="contact">
                        @if($user->contactDetail != null)
                            @include('partials.users.contactdetails', [
                                'contactdetail' => $user->contactDetail,
                                'organization'  => \App\Organization::find($user->current_organization_id)
                                ])
                        @else
                            <a
                                id="add-plan"
                                class="btn btn-success"
                                href="{{ route("contactdetails.create") }}">
                                {{ trans('global.contactdetail.create') }}
                            </a>
                        @endif
                    </div><!-- /.tab-pane -->
                    <div class="tab-pane show" id="notes">
                        <notes notable_type="App\User"
                               notable_id="{{ $user->id }}"
                               :show_tabs=false ></notes>
                    </div><!-- /.tab-pane -->

                </div><!-- /.tab-content -->
            </div><!-- /.card-body -->
        </div><!-- /.card -->

        @if (auth()->user()->role()->id == 1)
        <div class="card">
            <div class="card-header p-2">
                Debug
            </div><!-- /.card-header -->
            <div class="card-body">
                User
                @foreach([App\User::find($user->id)] as $usr)
                    <li class="small">id: {{ $usr->id }}; </li>
                    <li class="small">common_name: {{ $usr->common_name }}; </li>
                    <li class="small">firstname: {{ $usr->firstname }}; </li>
                    <li class="small">lastname: {{ $usr->lastname }}; </li>
                    <li class="small">email: {{ $usr->email }}; </li>
                    <li class="small">status: {{ $usr->status }}; </li>
                    <li class="small">current_organization_id: {{ $usr->current_organization_id }}; </li>
                    <li class="small">current_period_id: {{ $usr->current_period_id }}; </li>
                @endforeach
                <br/>
                currentCurriculaEnrolments:
                @foreach(App\User::find($user->id)->currentCurriculaEnrolments() as $cur_enr)
                    <li class="small">id: {{ $cur_enr->id }}; title: {{ $cur_enr->title }};  course_id: {{ $cur_enr->course_id }};  group_id: {{ $cur_enr->group_id }}; </li>
                @endforeach
                <br/>
                currentGroupEnrolments:
                @foreach(App\User::find($user->id)->currentGroupEnrolments as $grp_enr)
                    <li class="small">id: {{ $grp_enr->id }}; title: {{ $grp_enr->title }};  period_id: {{ $grp_enr->period_id }};  course_id: {{ $grp_enr->course_id }}; </li>
                @endforeach
                <br/>
                Groups:
                @foreach(App\User::find($user->id)->groups as $groups)
                    <li class="small">id: {{ $groups->id }}; title: {{ $groups->title }};  period_id: {{ $groups->period_id }};  organization_id: {{ $groups->organization_id }}; </li>
                @endforeach
                <br/>

                organizations:
                @foreach(App\User::find($user->id)->organizations as $org)
                    <li class="small">id: {{ $org->id }}; title: {{ $org->title }}; </li>
                @endforeach
                <br/>

            </div><!-- /.card-body -->
        </div><!-- /.card -->
            @endif
    </div>
</div>

<medium-create-modal></medium-create-modal>

@endsection

@section('scripts')
@parent
<script>

function setAvatar()
{
    $.ajax({
        headers: {'x-csrf-token': _token},
            method: 'POST',
            url: "{{ route('users.setAvatar') }}",
            data: {
                medium_id: $('#medium_id').val(),
                _method: 'PATCH',
            }
    })
    .done(function () { location.reload() })
}
</script>

@endsection
