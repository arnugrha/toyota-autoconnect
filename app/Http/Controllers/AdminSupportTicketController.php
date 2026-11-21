<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicket\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminSupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $ticketService
    ) {}

    /**
     * Display a listing of support tickets.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SupportTicket::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $baseQuery = SupportTicket::query();

        if ($user->branch_id && ! $user->hasRole('Super Admin')) {
            $baseQuery->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'waiting_customer' => (clone $baseQuery)->where('status', 'waiting_customer')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
        ];

        $query = (clone $baseQuery)->with(['user', 'branch', 'assignedAgent'])
            ->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('ticket_number', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $tickets = $query->paginate(12)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'branches' => $branches,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
                'priority' => $request->input('priority', 'all'),
                'category' => $request->input('category', 'all'),
                'branch_id' => $request->input('branch_id', ''),
            ],
        ]);
    }

    /**
     * Display a specific support ticket detail and thread.
     */
    public function show(SupportTicket $ticket): View
    {
        Gate::authorize('view', $ticket);

        $ticket->load([
            'user',
            'branch',
            'assignedAgent',
            'messages.user',
        ]);

        $agents = User::role(['CRM Officer', 'Dealer Manager', 'Service Advisor', 'Super Admin'])->orderBy('name')->get();

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'agents' => $agents,
        ]);
    }

    /**
     * Reply to the support ticket (public reply or internal staff note).
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        Gate::authorize('reply', $ticket);

        $validated = $request->validate([
            'message' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,docx', 'max:5120'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $isInternal = $request->boolean('is_internal');

        try {
            $this->ticketService->replyTicket(
                ticket: $ticket,
                sender: $user,
                message: $validated['message'],
                isInternal: $isInternal,
                attachment: $request->file('attachment')
            );

            $msg = $isInternal ? 'Catatan internal berhasil ditambahkan.' : 'Balasan tiket berhasil dikirim ke pelanggan.';

            return redirect()->route('admin.tickets.show', $ticket)->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update support ticket status.
     */
    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        Gate::authorize('manage', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,waiting_customer,resolved,closed'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->ticketService->updateStatus($ticket, $validated['status'], $user);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "Status tiket berhasil diperbarui menjadi '{$ticket->fresh()->status_label}'.");
    }

    /**
     * Assign ticket to an agent.
     */
    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        Gate::authorize('manage', $ticket);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $agent = $validated['assigned_to'] ? User::find($validated['assigned_to']) : null;

        $this->ticketService->assignAgent($ticket, $agent);

        $agentName = $agent ? $agent->name : 'Belum Ditugaskan';

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "Tiket berhasil ditugaskan ke {$agentName}.");
    }
}

