<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\WithdrawalRequest;

class WithdrawalRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $status;
    public $withdrawalRequest;

    public function __construct($status, WithdrawalRequest $withdrawalRequest)
    {
        $this->status = $status;
        $this->withdrawalRequest = $withdrawalRequest;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'status' => $this->status,
            'amount' => $this->withdrawalRequest->amount,
            'upi_id' => $this->withdrawalRequest->upi_id,
            'request_id' => $this->withdrawalRequest->id,
            'message' => $this->status === 'approved'
                ? 'Your withdrawal request has been approved.'
                : 'Your withdrawal request has been declined.',
        ];
    }
} 