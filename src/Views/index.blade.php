@extends($master)

@section('page')
    {{ trans('brazidesk::lang.index-title') }}
@stop

@section('content')
    @include('brazidesk::shared.header')
    @include('brazidesk::tickets.index')
@stop

@section('footer')
	<script src="//cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
	<script src="//cdn.datatables.net/plug-ins/505bef35b56/integration/bootstrap/3/dataTables.bootstrap.js"></script>
	<script src="//cdn.datatables.net/responsive/1.0.7/js/dataTables.responsive.min.js"></script>
	<script>
	    $('.table').DataTable({
	        processing: false,
	        serverSide: true,
	        responsive: true,
            pageLength: {{ $setting->grab('paginate_items') }},
        	lengthMenu: {{ json_encode($setting->grab('length_menu')) }},
	        ajax: '{!! route($setting->grab('main_route').'.data', $complete) !!}',
	        language: {
				decimal:        "{{ trans('brazidesk::lang.table-decimal') }}",
				emptyTable:     "{{ trans('brazidesk::lang.table-empty') }}",
				info:           "{{ trans('brazidesk::lang.table-info') }}",
				infoEmpty:      "{{ trans('brazidesk::lang.table-info-empty') }}",
				infoFiltered:   "{{ trans('brazidesk::lang.table-info-filtered') }}",
				infoPostFix:    "{{ trans('brazidesk::lang.table-info-postfix') }}",
				thousands:      "{{ trans('brazidesk::lang.table-thousands') }}",
				lengthMenu:     "{{ trans('brazidesk::lang.table-length-menu') }}",
				loadingRecords: "{{ trans('brazidesk::lang.table-loading-results') }}",
				processing:     "{{ trans('brazidesk::lang.table-processing') }}",
				search:         "{{ trans('brazidesk::lang.table-search') }}",
				zeroRecords:    "{{ trans('brazidesk::lang.table-zero-records') }}",
				paginate: {
					first:      "{{ trans('brazidesk::lang.table-paginate-first') }}",
					last:       "{{ trans('brazidesk::lang.table-paginate-last') }}",
					next:       "{{ trans('brazidesk::lang.table-paginate-next') }}",
					previous:   "{{ trans('brazidesk::lang.table-paginate-prev') }}"
				},
				aria: {
					sortAscending:  "{{ trans('brazidesk::lang.table-aria-sort-asc') }}",
					sortDescending: "{{ trans('brazidesk::lang.table-aria-sort-desc') }}"
				},
			},
	        columns: [
	            { data: 'id', name: 'brazidesk.id' },
	            { data: 'subject', name: 'subject' },
	            { data: 'status', name: 'brazidesk_statuses.name' },
	            { data: 'updated_at', name: 'brazidesk.updated_at' },
            	{ data: 'agent', name: 'users.name' },
	            @if( $u->isAgent() || $u->isAdmin() )
		            { data: 'priority', name: 'brazidesk_priorities.name' },
	            	{ data: 'owner', name: 'users.name' },
		            { data: 'category', name: 'brazidesk_categories.name' }
	            @endif
	        ]
	    });
	</script>
@append
