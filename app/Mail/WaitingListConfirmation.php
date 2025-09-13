<?php

namespace App\Mail;

use App\Models\WaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitingListConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WaitingList $waitingListEntry
    ) {}

    public function build()
    {
        $confirmationUrl = url('/waiting-list/confirm/' . $this->waitingListEntry->confirmation_token);

        return $this->subject('🌟 Bienvenue sur la Waiting List de Stellar')
                    ->view('emails.waiting-list-confirmation')
                    ->with([
                        'entry' => $this->waitingListEntry,
                        'confirmationUrl' => $confirmationUrl,
                        'firstName' => $this->waitingListEntry->first_name,
                        'fullName' => $this->waitingListEntry->full_name,
                        'interestLabel' => $this->waitingListEntry->interest_label,
                    ]);
    }
}
