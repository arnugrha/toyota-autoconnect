<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\Branch;
use App\Models\SupportTicket;
use App\Services\SupportTicket\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerSupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $ticketService
    ) {}

    /**
     * Display a listing of customer's support tickets.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SupportTicket::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = SupportTicket::with(['branch', 'assignedAgent'])
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tickets = $query->paginate(10)->withQueryString();

        return view('customer.tickets.index', [
            'tickets' => $tickets,
            'currentStatus' => $request->input('status'),
        ]);
    }

    /**
     * Show form to create a new support ticket.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', SupportTicket::class);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('customer.tickets.create', [
            'branches' => $branches,
            'preselectedCategory' => $request->input('category'),
            'preselectedSubject' => $request->input('subject'),
        ]);
    }

    /**
     * Store a newly created support ticket.
     */
    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        Gate::authorize('create', SupportTicket::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ticket = $this->ticketService->createTicket(
            customer: $user,
            data: $request->validated(),
            attachment: $request->file('attachment')
        );

        return redirect()->route('customer.tickets.show', $ticket)
            ->with('success', "Tiket bantuan {$ticket->ticket_number} berhasil dibuat. Tim Customer Care kami akan segera merespons.");
    }

    /**
     * Display conversation thread of a support ticket.
     */
    public function show(SupportTicket $ticket): View
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['branch', 'assignedAgent', 'customerMessages.user']);

        return view('customer.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Customer sends a reply in the ticket thread.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        Gate::authorize('reply', $ticket);

        $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->ticketService->replyTicket(
            ticket: $ticket,
            sender: $user,
            message: $request->input('message'),
            isInternal: false,
            attachment: $request->file('attachment')
        );

        return redirect()->route('customer.tickets.show', $ticket)
            ->with('success', 'Balasan pesan Anda berhasil dikirim.');
    }
}

