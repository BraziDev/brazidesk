@extends($master)
@section('page', trans('brazidesk::admin.priority-create-title'))

@section('content')
    @include('brazidesk::shared.header')
    <div class="well bs-component">
        {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.priority.store', 'method' => 'POST', 'class' => 'form-horizontal']) !!}
            <legend>{{ trans('brazidesk::admin.priority-create-title') }}</legend>
            @include('brazidesk::admin.priority.form')
        {!! CollectiveForm::close() !!}
    </div>
@stop
