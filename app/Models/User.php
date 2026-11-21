<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get primary role display name.
     */
    protected function roleName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->roles->first()?->name ?? 'Customer'
        );
    }

    /**
     * Determine if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Determine if the user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('Customer');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function customerLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'customer_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function testDriveBookings(): HasMany
    {
        return $this->hasMany(TestDriveBooking::class);
    }

    public function assignedTestDrives(): HasMany
    {
        return $this->hasMany(TestDriveBooking::class, 'assigned_to');
    }

    public function salesOpportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class);
    }

    public function assignedSalesOpportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class, 'assigned_to');
    }

    public function salesOffers(): HasMany
    {
        return $this->hasMany(SalesOffer::class, 'customer_id');
    }

    public function creditSimulations(): HasMany
    {
        return $this->hasMany(CreditSimulation::class);
    }

    public function leasingApplications(): HasMany
    {
        return $this->hasMany(LeasingApplication::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function vehicleBookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class, 'customer_id');
    }

    public function serviceBookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function serviceReminders(): HasMany
    {
        return $this->hasMany(ServiceReminder::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function getNotificationPreferences(): NotificationPreference
    {
        return NotificationPreference::firstOrCreate(
            ['user_id' => $this->id],
            [
                'in_app' => true,
                'email' => true,
                'whatsapp' => false,
                'service_reminders' => true,
                'transactional' => true,
                'promotional' => false,
                'whatsapp_consent' => false,
            ]
        );
    }
}
