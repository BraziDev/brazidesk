@extends($master)

@section('page')
    {{ trans('brazidesk::admin.category-index-title') }}
@stop

@section('content')
    @include('brazidesk::shared.header')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ trans('brazidesk::admin.category-index-title') }}
                {!! link_to_route(
                                    $setting->grab('admin_route').'.category.create',
                                    trans('brazidesk::admin.btn-create-new-category'), null,
                                    ['class' => 'btn btn-primary pull-right'])
                !!}
            </h2>
        </div>

        @if ($categories->isEmpty())
            <h3 class="text-center">{{ trans('brazidesk::admin.category-index-no-categories') }}
                {!! link_to_route($setting->grab('admin_route').'.category.create', trans('brazidesk::admin.category-index-create-new')) !!}
            </h3>
        @else
            <div id="message"></div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <td>{{ trans('brazidesk::admin.table-id') }}</td>
                        <td>{{ trans('brazidesk::admin.table-name') }}</td>
                        <td>{{ trans('brazidesk::admin.table-action') }}</td>
                    </tr>
                </thead>
                <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td style="vertical-align: middle">
                            {{ $category->id }}
                        </td>
                        <td style="color: {{ $category->color }}; vertical-align: middle">
                            {{ $category->name }}
                        </td>
                        <td>
                            {!! link_to_route(
                                                    $setting->grab('admin_route').'.category.edit', trans('brazidesk::admin.btn-edit'), $category->id,
                                                    ['class' => 'btn btn-info'] )
                                !!}

                                {!! link_to_route(
                                                    $setting->grab('admin_route').'.category.destroy', trans('brazidesk::admin.btn-delete'), $category->id,
                                                    [
                                                    'class' => 'btn btn-danger deleteit',
                                                    'form' => "delete-$category->id",
                                                    "node" => $category->name
                                                    ])
                                !!}
                            {!! CollectiveForm::open([
                                            'method' => 'DELETE',
                                            'route' => [
                                                        $setting->grab('admin_route').'.category.destroy',
                                                        $category->id
                                                        ],
                                            'id' => "delete-$category->id"
                                            ])
                            !!}
                            {!! CollectiveForm::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@stop
@section('footer')
    <script>
        $( ".deleteit" ).click(function( event ) {
            event.preventDefault();
            if (confirm("{!! trans('brazidesk::admin.category-index-js-delete') !!}" + $(this).attr("node") + " ?"))
            {
                var form = $(this).attr("form");
                $("#" + form).submit();
            }

        });
    </script>
@append
