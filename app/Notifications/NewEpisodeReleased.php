<?php

namespace App\Notifications;

use App\Models\TvShow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEpisodeReleased extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TvShow $tvShow,
        public int $seasonNumber,
        public int $episodeNumber,
        public string $episodeName
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tv_show_id' => $this->tvShow->id,
            'tv_show_name' => $this->tvShow->name,
            'season_number' => $this->seasonNumber,
            'episode_number' => $this->episodeNumber,
            'episode_name' => $this->episodeName,
            'message' => "A new episode of {$this->tvShow->name} is now available! (S{$this->seasonNumber}E{$this->episodeNumber} - {$this->episodeName})",
        ];
    }
}
