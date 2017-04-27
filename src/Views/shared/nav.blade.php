<div class="panel panel-default">
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li role="presentation" class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\TicketsController@index')) ? "active" : "" !!}">
                <a href="{{ action('\Brazidev\Brazidesk\Controllers\TicketsController@index') }}">{{ trans('brazidesk::lang.nav-active-tickets') }}
                    <span class="badge">
                         <?php 
                            if ($u->isAdmin()) {
                                echo Brazidev\Brazidesk\Models\Ticket::active()->count();
                            } elseif ($u->isAgent()) {
                                echo Brazidev\Brazidesk\Models\Ticket::active()->agentUserTickets($u->id)->count();
                            } else {
                                echo Brazidev\Brazidesk\Models\Ticket::userTickets($u->id)->active()->count();
                            }
                        ?>
                    </span>
                </a>
            </li>
            <li role="presentation" class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\TicketsController@indexComplete')) ? "active" : "" !!}">
                <a href="{{ action('\Brazidev\Brazidesk\Controllers\TicketsController@indexComplete') }}">{{ trans('brazidesk::lang.nav-completed-tickets') }}
                    <span class="badge">
                        <?php 
                            if ($u->isAdmin()) {
                                echo Brazidev\Brazidesk\Models\Ticket::complete()->count();
                            } elseif ($u->isAgent()) {
                                echo Brazidev\Brazidesk\Models\Ticket::complete()->agentUserTickets($u->id)->count();
                            } else {
                                echo Brazidev\Brazidesk\Models\Ticket::userTickets($u->id)->complete()->count();
                            }
                        ?>
                    </span>
                </a>
            </li>

            @if($u->isAdmin())
                <li role="presentation" class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\DashboardController@index')) || Request::is($setting->grab('admin_route').'/indicator*') ? "active" : "" !!}">
                    <a href="{{ action('\Brazidev\Brazidesk\Controllers\DashboardController@index') }}">{{ trans('brazidesk::admin.nav-dashboard') }}</a>
                </li>

                <li role="presentation" class="dropdown {!!
                    $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\StatusesController@index').'*') ||
                    $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\PrioritiesController@index').'*') ||
                    $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\AgentsController@index').'*') ||
                    $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\CategoriesController@index').'*') ||
                    $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\ConfigurationsController@index').'*') ||
                    $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\AdministratorsController@index').'*')
                    ? "active" : "" !!}">

                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        {{ trans('brazidesk::admin.nav-settings') }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li role="presentation" class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\StatusesController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Brazidev\Brazidesk\Controllers\StatusesController@index') }}">{{ trans('brazidesk::admin.nav-statuses') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\PrioritiesController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Brazidev\Brazidesk\Controllers\PrioritiesController@index') }}">{{ trans('brazidesk::admin.nav-priorities') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\AgentsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Brazidev\Brazidesk\Controllers\AgentsController@index') }}">{{ trans('brazidesk::admin.nav-agents') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\CategoriesController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Brazidev\Brazidesk\Controllers\CategoriesController@index') }}">{{ trans('brazidesk::admin.nav-categories') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\ConfigurationsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Brazidev\Brazidesk\Controllers\ConfigurationsController@index') }}">{{ trans('brazidesk::admin.nav-configuration') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Brazidev\Brazidesk\Controllers\AdministratorsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Brazidev\Brazidesk\Controllers\AdministratorsController@index')}}">{{ trans('brazidesk::admin.nav-administrator') }}</a>
                        </li>
                    </ul>
                </li>
            @endif

        </ul>
    </div>
</div>
