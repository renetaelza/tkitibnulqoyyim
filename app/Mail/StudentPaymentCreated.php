<?php

namespace App\Mail;

use App\Models\StudentPayment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentPaymentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentPayment $studentPayment,
        public ?User $recipientUser = null
    ) {
    }

    public function envelope(): Envelope
    {
        $paymentName = $this->studentPayment->payment?->name ?? 'Pembayaran';

        return new Envelope(
            subject: 'Tagihan baru - ' . $paymentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-payment-created',
            with: [
                'studentPayment' => $this->studentPayment,
                'recipientUser' => $this->recipientUser,
                'billUrl' => route('dashboard.bills'),
            ],
        );
    }
}
