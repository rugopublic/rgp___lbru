<?php

namespace App\Mail\Admin;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Initializes the marketing campaign mail instance and wires its required dependencies.
     *
     * @param EmailCampaign $campaign
     * @param User $user
     * @return void
     */
    public function __construct(
        public EmailCampaign $campaign,
        public User $user,
    ) {}

    /**
     * Handles envelope for the marketing campaign mail.
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    /**
     * Handles content for the marketing campaign mail.
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.marketing-campaign',
            with: [
                'unsubscribeUrl' => URL::signedRoute('marketing.unsubscribe', [
                    'user' => $this->user->public_id,
                    'campaign' => $this->campaign->id,
                ]),
            ],
        );
    }
}
