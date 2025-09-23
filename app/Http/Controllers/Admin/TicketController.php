<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function checkAdmin()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    // Show all tickets and analytics
    public function index(Request $request)
    {
        $this->checkAdmin();
        $status = $request->query('status');
        $query = Ticket::with(['user', 'agent'])
            ->where('created_at', '>=', now()->subDays(30));
        if (in_array($status, ['pending', 'resolved'])) {
            $query->where('status', $status);
        }
        $tickets = $query->latest()->paginate(20);
        $totalTickets = Ticket::where('created_at', '>=', now()->subDays(30))->count();
        $resolvedTickets = Ticket::where('status', 'resolved')->where('created_at', '>=', now()->subDays(30))->count();
        $pendingTickets = Ticket::where('status', 'pending')->where('created_at', '>=', now()->subDays(30))->count();
        return view('admin.tickets.index', compact('tickets', 'totalTickets', 'resolvedTickets', 'pendingTickets', 'status'));
    }

    // Update ticket status (resolve or set pending)
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $ticket = Ticket::findOrFail($id);
        $request->validate([
            'status' => 'required|in:pending,resolved',
        ]);
        $ticket->status = $request->status;
        $ticket->save();
        return redirect()->back()->with('success', 'Ticket status updated.');
    }

    /**
     * Export tickets as CSV
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        $this->checkAdmin();
        $status = $request->query('status');
        $query = Ticket::with(['user', 'agent'])
            ->where('created_at', '>=', now()->subDays(30));
        if (in_array($status, ['pending', 'resolved'])) {
            $query->where('status', $status);
        }
        $tickets = $query->latest()->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tickets.csv"',
        ];
        $columns = ['ID', 'Name', 'Description', 'User/Agent', 'Phone Number', 'Status', 'Created At'];
        $callback = function() use ($tickets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($tickets as $ticket) {
                if ($ticket->user) {
                    $userAgent = 'User: ' . $ticket->user->name;
                    $phoneNumber = $ticket->user->phone_number ?? 'N/A';
                } elseif ($ticket->agent) {
                    $userAgent = 'Agent: ' . $ticket->agent->name;
                    $phoneNumber = $ticket->agent->phone_number ?? 'N/A';
                } else {
                    $userAgent = 'N/A';
                    $phoneNumber = 'N/A';
                }
                
                fputcsv($file, [
                    $ticket->id,
                    $ticket->name,
                    $ticket->description,
                    $userAgent,
                    $phoneNumber,
                    ucfirst($ticket->status),
                    $ticket->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
} 