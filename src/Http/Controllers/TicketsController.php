<?php

namespace Brazidev\Brazidesk\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Brazidev\Brazidesk\Models;
use Brazidev\Brazidesk\Models\Agent;
use Brazidev\Brazidesk\Models\Category;
use Brazidev\Brazidesk\Models\Setting;
use Brazidev\Brazidesk\Models\Ticket;
use Yajra\Datatables\Datatables;
use Yajra\Datatables\Engines\EloquentEngine;

class TicketsController extends Controller
{
    protected $tickets;
    protected $agent;

    public function __construct(Ticket $tickets, Agent $agent)
    {
        $this->middleware('Brazidev\Brazidesk\Middleware\ResAccessMiddleware', ['only' => ['show']]);
        $this->middleware('Brazidev\Brazidesk\Middleware\IsAgentMiddleware', ['only' => ['edit', 'update']]);
        $this->middleware('Brazidev\Brazidesk\Middleware\IsAdminMiddleware', ['only' => ['destroy']]);

        $this->tickets = $tickets;
        $this->agent = $agent;
    }

    public function data(Datatables $datatables, $complete = false)
    {
        $user = $this->agent->find(auth()->user()->id);

        if ($user->isAdmin()) {
            if ($complete) {
                $collection = Ticket::complete();
            } else {
                $collection = Ticket::active();
            }
        } elseif ($user->isAgent()) {
            if ($complete) {
                $collection = Ticket::complete()->agentUserTickets($user->id);
            } else {
                $collection = Ticket::active()->agentUserTickets($user->id);
            }
        } else {
            if ($complete) {
                $collection = Ticket::userTickets($user->id)->complete();
            } else {
                $collection = Ticket::userTickets($user->id)->active();
            }
        }

        $collection
            ->join('users', 'users.id', '=', 'brazidesk.user_id')
            ->join('brazidesk_statuses', 'brazidesk_statuses.id', '=', 'brazidesk.status_id')
            ->join('brazidesk_priorities', 'brazidesk_priorities.id', '=', 'brazidesk.priority_id')
            ->join('brazidesk_categories', 'brazidesk_categories.id', '=', 'brazidesk.category_id')
            ->select([
                'brazidesk.id',
                'brazidesk.subject AS subject',
                'brazidesk_statuses.name AS status',
                'brazidesk_statuses.color AS color_status',
                'brazidesk_priorities.color AS color_priority',
                'brazidesk_categories.color AS color_category',
                'brazidesk.id AS agent',
                'brazidesk.updated_at AS updated_at',
                'brazidesk_priorities.name AS priority',
                'users.name AS owner',
                'brazidesk.agent_id',
                'brazidesk_categories.name AS category',
            ]);

        $collection = $datatables->of($collection);

        $this->renderTicketTable($collection);

        $collection->editColumn('updated_at', '{!! \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $updated_at)->diffForHumans() !!}');

        return $collection->make(true);
    }

    public function renderTicketTable(EloquentEngine $collection)
    {
        $collection->editColumn('subject', function ($ticket) {
            return (string) link_to_route(
                Setting::grab('main_route').'.show',
                $ticket->subject,
                $ticket->id
            );
        });

        $collection->editColumn('status', function ($ticket) {
            $color = $ticket->color_status;
            $status = $ticket->status;

            return "<div style='color: $color'>$status</div>";
        });

        $collection->editColumn('priority', function ($ticket) {
            $color = $ticket->color_priority;
            $priority = $ticket->priority;

            return "<div style='color: $color'>$priority</div>";
        });

        $collection->editColumn('category', function ($ticket) {
            $color = $ticket->color_category;
            $category = $ticket->category;

            return "<div style='color: $color'>$category</div>";
        });

        $collection->editColumn('agent', function ($ticket) {
            $ticket = $this->tickets->find($ticket->id);

            return $ticket->agent->name;
        });

        return $collection;
    }

    /**
     * Display a listing of active tickets related to user.
     *
     * @return Response
     */
    public function index()
    {
        $complete = false;

        return view('brazidesk::index', compact('complete'));
    }

    /**
     * Display a listing of completed tickets related to user.
     *
     * @return Response
     */
    public function indexComplete()
    {
        $complete = true;

        return view('brazidesk::index', compact('complete'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $priorities = Models\Priority::pluck('name', 'id');
            $categories = Models\Category::pluck('name', 'id');
        } else { // if Laravel 5.1
            $priorities = Models\Priority::lists('name', 'id');
            $categories = Models\Category::lists('name', 'id');
        }

        return view('brazidesk::tickets.create', compact('priorities', 'categories'));
    }

    /**
     * Store a newly created ticket and auto assign an agent for it.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:brazidesk_priorities,id',
            'category_id' => 'required|exists:brazidesk_categories,id',
        ]);

        $ticket = new Ticket();

        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content'));

        $ticket->priority_id = $request->priority_id;
        $ticket->category_id = $request->category_id;

        $ticket->status_id = Setting::grab('default_status_id');
        $ticket->user_id = auth()->user()->id;
        $ticket->autoSelectAgent();

        $ticket->save();

        session()->flash('status', trans('brazidesk::lang.the-ticket-has-been-created'));

        return redirect()->action('\Brazidev\Brazidesk\Controllers\TicketsController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $ticket = $this->tickets->find($id);

        if (version_compare(app()->version(), '5.3.0', '>=')) {
            $status_lists = Models\Status::pluck('name', 'id');
            $priority_lists = Models\Priority::pluck('name', 'id');
            $category_lists = Models\Category::pluck('name', 'id');
        } else { // if Laravel 5.1
            $status_lists = Models\Status::lists('name', 'id');
            $priority_lists = Models\Priority::lists('name', 'id');
            $category_lists = Models\Category::lists('name', 'id');
        }

        $close_perm = $this->permToClose($id);
        $reopen_perm = $this->permToReopen($id);

        $cat_agents = Models\Category::find($ticket->category_id)->agents()->agentsLists();
        if (is_array($cat_agents)) {
            $agent_lists = ['auto' => 'Auto Select'] + $cat_agents;
        } else {
            $agent_lists = ['auto' => 'Auto Select'];
        }

        $comments = $ticket->comments()->paginate(Setting::grab('paginate_items'));

        return view('brazidesk::tickets.show',
            compact('ticket', 'status_lists', 'priority_lists', 'category_lists', 'agent_lists', 'comments',
                'close_perm', 'reopen_perm'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:brazidesk_priorities,id',
            'category_id' => 'required|exists:brazidesk_categories,id',
            'status_id'   => 'required|exists:brazidesk_statuses,id',
            'agent_id'    => 'required',
        ]);

        $ticket = $this->tickets->findOrFail($id);

        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content'));

        $ticket->status_id = $request->status_id;
        $ticket->category_id = $request->category_id;
        $ticket->priority_id = $request->priority_id;

        if ($request->input('agent_id') == 'auto') {
            $ticket->autoSelectAgent();
        } else {
            $ticket->agent_id = $request->input('agent_id');
        }

        $ticket->save();

        session()->flash('status', trans('brazidesk::lang.the-ticket-has-been-modified'));

        return redirect()->route(Setting::grab('main_route').'.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $ticket = $this->tickets->findOrFail($id);
        $subject = $ticket->subject;
        $ticket->delete();

        session()->flash('status', trans('brazidesk::lang.the-ticket-has-been-deleted', ['name' => $subject]));

        return redirect()->route(Setting::grab('main_route').'.index');
    }

    /**
     * Mark ticket as complete.
     *
     * @param int $id
     *
     * @return Response
     */
    public function complete($id)
    {
        if ($this->permToClose($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = Carbon::now();

            if (Setting::grab('default_close_status_id')) {
                $ticket->status_id = Setting::grab('default_close_status_id');
            }

            $subject = $ticket->subject;
            $ticket->save();

            session()->flash('status', trans('brazidesk::lang.the-ticket-has-been-completed', ['name' => $subject]));

            return redirect()->route(Setting::grab('main_route').'.index');
        }

        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('brazidesk::lang.you-are-not-permitted-to-do-this'));
    }

    /**
     * Reopen ticket from complete status.
     *
     * @param int $id
     *
     * @return Response
     */
    public function reopen($id)
    {
        if ($this->permToReopen($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = null;

            if (Setting::grab('default_reopen_status_id')) {
                $ticket->status_id = Setting::grab('default_reopen_status_id');
            }

            $subject = $ticket->subject;
            $ticket->save();

            session()->flash('status', trans('brazidesk::lang.the-ticket-has-been-reopened', ['name' => $subject]));

            return redirect()->route(Setting::grab('main_route').'.index');
        }

        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('brazidesk::lang.you-are-not-permitted-to-do-this'));
    }

    public function agentSelectList($category_id, $ticket_id)
    {
        $cat_agents = Models\Category::find($category_id)->agents()->agentsLists();
        if (is_array($cat_agents)) {
            $agents = ['auto' => 'Auto Select'] + $cat_agents;
        } else {
            $agents = ['auto' => 'Auto Select'];
        }

        $selected_Agent = $this->tickets->find($ticket_id)->agent->id;
        $select = '<select class="form-control" id="agent_id" name="agent_id">';
        foreach ($agents as $id => $name) {
            $selected = ($id == $selected_Agent) ? 'selected' : '';
            $select .= '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
        }
        $select .= '</select>';

        return $select;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToClose($id)
    {
        $close_ticket_perm = Setting::grab('close_ticket_perm');

        if ($this->agent->isAdmin() && $close_ticket_perm['admin'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isAgent() && $close_ticket_perm['agent'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isTicketOwner($id) && $close_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToReopen($id)
    {
        $reopen_ticket_perm = Setting::grab('reopen_ticket_perm');
        if ($this->agent->isAdmin() && $reopen_ticket_perm['admin'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isAgent() && $reopen_ticket_perm['agent'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isTicketOwner($id) && $reopen_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * Calculate average closing period of days per category for number of months.
     *
     * @param int $period
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function monthlyPerfomance($period = 2)
    {
        $categories = Category::all();
        foreach ($categories as $cat) {
            $records['categories'][] = $cat->name;
        }

        for ($m = $period; $m >= 0; $m--) {
            $from = Carbon::now();
            $from->day = 1;
            $from->subMonth($m);
            $to = Carbon::now();
            $to->day = 1;
            $to->subMonth($m);
            $to->endOfMonth();
            $records['interval'][$from->format('F Y')] = [];
            foreach ($categories as $cat) {
                $records['interval'][$from->format('F Y')][] = round($this->intervalPerformance($from, $to, $cat->id), 1);
            }
        }

        return $records;
    }

    /**
     * Calculate the date length it took to solve a ticket.
     *
     * @param Ticket $ticket
     *
     * @return int|false
     */
    public function ticketPerformance($ticket)
    {
        if ($ticket->completed_at == null) {
            return false;
        }

        $created = new Carbon($ticket->created_at);
        $completed = new Carbon($ticket->completed_at);
        $length = $created->diff($completed)->days;

        return $length;
    }

    /**
     * Calculate the average date length it took to solve tickets within date period.
     *
     * @param $from
     * @param $to
     *
     * @return int
     */
    public function intervalPerformance($from, $to, $cat_id = false)
    {
        if ($cat_id) {
            $tickets = Ticket::where('category_id', $cat_id)->whereBetween('completed_at', [$from, $to])->get();
        } else {
            $tickets = Ticket::whereBetween('completed_at', [$from, $to])->get();
        }

        if (empty($tickets->first())) {
            return false;
        }

        $performance_count = 0;
        $counter = 0;
        foreach ($tickets as $ticket) {
            $performance_count += $this->ticketPerformance($ticket);
            $counter++;
        }
        $performance_average = $performance_count / $counter;

        return $performance_average;
    }
}
