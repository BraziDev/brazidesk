@extends($master)

@section('page')
    {{ trans('brazidesk::admin.status-index-title') }}
@stop

@section('content')
    @include('brazidesk::shared.header')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ trans('brazidesk::admin.status-index-title') }}
                {!! link_to_route(
                                    $setting->grab('admin_route').'.status.create',
                                    trans('brazidesk::admin.btn-create-new-status'), null,
                                    ['class' => 'btn btn-primary pull-right'])
                !!}
            </h2>
        </div>

        @if ($statuses->isEmpty())
            <h3 class="text-center">{{ trans('brazidesk::admin.status-index-no-statuses') }}
                {!! link_to_route($setting->grab('admin_route').'.status.create', trans('brazidesk::admin.status-index-create-new')) !!}
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
                @foreach($statuses as $status)
                    <tr>
                        <td style="vertical-align: middle">
                            {{ $status->id }}
                        </td>
                        <td style="color: {{ $status->color }}; vertical-align: middle">
                            {{ $status->name }}
                        </td>
                        <td>
                            {!! link_to_route(
                                                    $setting->grab('admin_route').'.status.edit', trans('brazidesk::admin.btn-edit'), $status->id,
                                                    ['class' => 'btn btn-info'] )
                                !!}

                                {!! link_to_route(
                                                    $setting->grab('admin_route').'.status.destroy', trans('brazidesk::admin.btn-delete'), $status->id,
                                                    [
                                                    'class' => 'btn btn-danger deleteit',
                                                    'form' => "delete-$status->id",
                                                    "node" => $status->name
                                                    ])
                                !!}
                            {!! CollectiveForm::open([
                                            'method' => 'DELETE',
                                            'route' => [
                                                        $setting->grab('admin_route').'.status.destroy',
                                                        $status->id
                                                        ],
                                            'id' => "delete-$status->id"
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
            if (confirm("{!! trans('brazidesk::admin.status-index-js-delete') !!}" + $(this).attr("node") + " ?"))
            {
                $form = $(this).attr("form");
                $("#" + $form).submit();
            }

        });
    </script>
@append
