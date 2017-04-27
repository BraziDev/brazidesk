<table class="table table-condensed table-stripe ddt-responsive" class="brazidesk-table">
    <thead>
        <tr>
            <td>{{ trans('brazidesk::lang.table-id') }}</td>
            <td>{{ trans('brazidesk::lang.table-subject') }}</td>
            <td>{{ trans('brazidesk::lang.table-status') }}</td>
            <td>{{ trans('brazidesk::lang.table-last-updated') }}</td>
            <td>{{ trans('brazidesk::lang.table-agent') }}</td>
          @if( $u->isAgent() || $u->isAdmin() )
            <td>{{ trans('brazidesk::lang.table-priority') }}</td>
            <td>{{ trans('brazidesk::lang.table-owner') }}</td>
            <td>{{ trans('brazidesk::lang.table-category') }}</td>
          @endif
        </tr>
    </thead>
</table>