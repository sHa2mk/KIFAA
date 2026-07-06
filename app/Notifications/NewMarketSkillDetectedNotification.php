<?php

namespace App\Notifications;

use App\Models\MissingSkill;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMarketSkillDetectedNotification extends Notification
{
    use Queueable;

    /**
     * Creates a new market skill notification instance.
     *
     * @param  \App\Models\MissingSkill  $missingSkill
     * @return void
     */
    public function __construct(
        public MissingSkill $missingSkill
    ) {}

    /**
     * Defines the notification delivery channels.
     *
     * The database channel is used because the alert should appear inside the
     * platform notification list instead of being sent as an email.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Builds the data stored in the notifications table.
     *
     * The stored data is used to show the user that a new job market skill was
     * added to their missing skills list.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New job market skill detected',
            'message' => 'A new skill was added to your missing skills: ' . $this->missingSkill->skill->name,
            'skill_id' => $this->missingSkill->skill_id,
            'missing_skill_id' => $this->missingSkill->id,
            'type' => 'new_market_skill',
        ];
    }
}