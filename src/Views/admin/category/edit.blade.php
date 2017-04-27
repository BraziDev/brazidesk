@extends($master)
@section('page', trans('brazidesk::admin.category-edit-title', ['name' => ucwords($category->name)]))

@section('content')
    @include('brazidesk::shared.header')
    <div class="well bs-component">
        {!! CollectiveForm::model($category, [
                                    'route' => [$setting->grab('admin_route').'.category.update', $category->id],
                                    'method' => 'PATCH',
                                    'class' => 'form-horizontal'
                                    ]) !!}
        <legend>{{ trans('brazidesk::admin.category-edit-title', ['name' => ucwords($category->name)]) }}</legend>
        @include('brazidesk::admin.category.form', ['update', true])
        {!! CollectiveForm::close() !!}
    </div>
@stop
