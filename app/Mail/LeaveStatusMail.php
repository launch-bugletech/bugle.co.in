<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Leave;

class LeaveStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;
    public $status;

    public function __construct(Leave $leave, $status)
    {
        $this->leave = $leave;
        $this->status = $status;
    }

    public function build()
    {
        // Dynamic subject based on status
        switch ($this->status) {

            case 'Approved':
                $subject = 'Leave Approved - ' . $this->leave->user->first_name;
                break;

            case 'Rejected':
                $subject = 'Leave Rejected - ' . $this->leave->user->first_name;
                break;

            case 'Cancelled':

                if (auth()->check() && auth()->user()->role_id == 1) {
                    $subject = 'Leave Cancelled by Admin - ' . $this->leave->user->first_name;
                } else {
                    $subject = 'Leave Cancelled by Employee - ' . $this->leave->user->first_name;
                }

                break;


            default:
                $subject = 'Leave Status Update';
        }

        return $this->subject($subject)
            ->view('email.leave-status')
            ->with([
                'leave' => $this->leave,
                'status' => $this->status
            ]);
    }
}
