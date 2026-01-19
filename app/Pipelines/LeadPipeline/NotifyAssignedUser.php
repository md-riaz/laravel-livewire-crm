<?php

namespace App\Pipelines\LeadPipeline;

use App\Models\Lead;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Notification;

/**
 * Notify Assigned User Pipeline Stage
 *
 * Sends notification to the user when a lead is assigned to them.
 * Can be used after lead creation or assignment changes.
 */
class NotifyAssignedUser
{
    /**
     * Handle the notification sending
     *
     * @param Lead|array<string, mixed> $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(Lead|array $data, Closure $next): mixed
    {
        // Skip if notifications are disabled
        if (!config('crm.pipeline.notify_on_assignment', true)) {
            return $next($data);
        }

        // Extract user ID whether we have a Lead model or array
        $userId = $data instanceof Lead 
            ? $data->assigned_to_user_id 
            : ($data['assigned_to_user_id'] ?? null);

        if ($userId) {
            $user = User::find($userId);
            
            if ($user) {
                // Here you would send actual notification
                // For now, we'll just log it or prepare for future implementation
                
                // Example: Notification::send($user, new LeadAssignedNotification($lead));
                
                // Log the notification intent
                logger()->info('Lead assigned notification would be sent', [
                    'user_id' => $user->id,
                    'lead_id' => $data instanceof Lead ? $data->id : null,
                ]);
            }
        }

        return $next($data);
    }
}
