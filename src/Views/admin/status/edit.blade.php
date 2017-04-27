@extends($master)
@section('page', trans('brazidesk::admin.status-edit-title', ['name' => ucwords($status->name)]))

@section('content')
    @include('brazidesk::shared.header')
    <div class="well bs-component">
        {!! CollectiveForm::model($status, [
                                    'route' => [$setting->grab('admin_route').'.status.update', $status->id],
                                    'method' => 'PATCH',
                                    'class' => 'form-horizontal'
                                    ]) !!}
        <legend>{{ trans('brazidesk::admin.status-edit-title', ['name' => ucwords($status->name)]) }}</legend>
        @include('brazidesk::admin.status.form', ['update', true])
        {!! CollectiveForm::close() !!}
    </div>
@stop
