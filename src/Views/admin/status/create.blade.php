@extends($master)
@section('page', trans('brazidesk::admin.status-create-title'))

@section('content')
    @include('brazidesk::shared.header')
    <div class="well bs-component">
        {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.status.store', 'method' => 'POST', 'class' => 'form-horizontal']) !!}
            <legend>{{ trans('brazidesk::admin.status-create-title') }}</legend>
            @include('brazidesk::admin.status.form')
        {!! CollectiveForm::close() !!}
    </div>
@stop
