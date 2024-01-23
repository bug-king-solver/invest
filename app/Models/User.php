<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use App\Stripe\SubscriptionsManager;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'city',
        'state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param bool $type
     * @return Subscription
     */
    public function currentSubscription($type = false)
    {
        $query = $this->subscriptions();
        if ($type) {
            $prefix = SubscriptionsManager::ADDON_SUBS[$type];
            $query->where('stripe_price', 'like', $prefix . '%');
        } else {
            foreach (SubscriptionsManager::ADDON_SUBS as $prefix) {
                $query->where('stripe_price', 'not like', $prefix . '%');
            }
        }

        return $query->first();
    }

    /**
     * @param bool|int $type
     * @return bool
     */
    public function hasActiveSubscription($type = false)
    {
        $sub = $this->currentSubscription($type);
        return is_null($sub) ? false : $sub->active() && $sub->stripe_status != 'canceled';
    }

    /**
     * @return bool
     */
    public function inBonusPeriod()
    {
        if ($this->type == 'merchant') {
            try {
                $current_subscription = $this->currentSubscription();
                return ($current_subscription->stripe_status == 'trialing' && $current_subscription->trial_ends_at->diffInDays($current_subscription->created_at) > 4);
            } catch (Throwable  $e) {
                return false;
            }
        }

        return false;
    }
}
