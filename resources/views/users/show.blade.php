@extends('layouts.admin')
@section('content')
<div class="row">
    <div class="col-4">
        <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                    
                  <img class="profile-user-img img-fluid img-circle" src="{{ Avatar::create($user->firstname.' '.$user->lastname)->toBase64() }}" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $user->firstname }} {{ $user->lastname }}</h3>

                <p class="text-muted text-center">{{ $user->username }} ({{ (null !== $user->currentRole()->first()) ? $user->currentRole()->first()->title : '' }})</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Accomplished</b> <a class="float-right">1,322</a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
        
        <div class="card card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="col-11">
                        <h5 class="m-0">
                            {{ trans('global.about') }}
                        </h5>
                    </div>
                    <div>
                        @can('user_edit')
                             <a href="{{ route('users.edit', $user->id) }}" >
                                <i class="far fa-edit"></i>
                             </a> 
                        @endcan 
                    </div>
                </div>
            </div>
              <!-- /.card-header -->
              <div class="card-body">
                <strong><i class="fa fa-users mr-1"></i>{{ trans('global.organization.title_singular') }}</strong>
                <p class="text-muted">
                    @foreach($user->organizations as $id => $organizations)
                        <button type="button" class="btn-xs btn-block btn-success pull-right">{{$organizations->title}} @ {{ $user->roles()->where('organization_id', $organizations->id)->first()->title }}</button>
                    @endforeach
                </p>

                <hr> 
                  
                <strong><i class="fa fa-users mr-1"></i>{{ trans('global.group.title_singular') }}</strong>
                <p class="text-muted">
                    @foreach($user->groups as $id => $groups)
                        <button type="button" class="btn-xs btn-block btn-success pull-right">{{$groups->title}} @ {{ $groups->organization->title }}</button>
                    @endforeach
                </p>

                <hr>
                <strong><i class="fa fa-key mr-1"></i>{{ trans('global.roles') }}</strong>
                <p class="text-muted">
                    @foreach($user->roles as $id => $roles)
                        <button type="button" class="btn-xs btn-block btn-success pull-right">{{$roles->title}} @ {{ $roles->organizations->first()->title }}</button>
                    @endforeach
                </p>

                <hr>

                <strong><i class="fa fa-envelope mr-1"></i> Contact</strong>

                <p class="text-muted">
                    {{ $user->email }}<br>
                    {{ trans('global.user.fields.email_verified_at') }} {{ $user->email_verified_at }}
                </p>

                <hr>

                <strong><i class="fa fa-file-alt mr-1"></i> Notes</strong>
                <p class="text-muted"></p>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <div class="float-left">
                    <button type="button" class="btn-xs btn-block btn-{{$statuses[$user->status_id]->color_css_class}} pull-right">{{$statuses[$user->status_id]->lang_de}}</button>                  
                </div>
                <small class="float-right">
                    {{ $user->updated_at }}
                </small> 
              </div>
        </div>
    </div>
    
    <div class="col-md-8">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active show" href="#activity" data-toggle="tab">Activity</a></li>
                  <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="tab-pane active show" id="activity">
                    Activity Tab
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="timeline">
                    <!-- The timeline -->
                    timeline
                  </div><!-- /.tab-pane -->

                  <div class="tab-pane" id="settings">
                    Organisational Settings
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div><!-- /.nav-tabs-custom -->
          </div>
</div>
@endsection