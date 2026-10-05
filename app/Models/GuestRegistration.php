<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestRegistration extends Model
{
    public const TRAVEL_TYPE_DOMESTIC = 'domestique';

    public const TRAVEL_TYPE_INTERNATIONAL = 'international';

    /** @var array<string, string> */
    public const TRAVEL_TYPE_LABELS = [
        self::TRAVEL_TYPE_DOMESTIC => 'Domestique',
        self::TRAVEL_TYPE_INTERNATIONAL => 'International',
    ];

    public const DOCUMENT_TYPE_CNI = 'cni';

    public const DOCUMENT_TYPE_PASSPORT = 'passeport';

    public const DOCUMENT_TYPE_DRIVER_LICENSE = 'permis_conduire';

    public const DOCUMENT_TYPE_CONSULAR_CARD = 'carte_consulaire';

    /** @var array<string, string> */
    public const DOCUMENT_TYPE_LABELS = [
        self::DOCUMENT_TYPE_CNI => 'Carte nationale d\'identité',
        self::DOCUMENT_TYPE_PASSPORT => 'Passeport',
        self::DOCUMENT_TYPE_DRIVER_LICENSE => 'Permis de conduire',
        self::DOCUMENT_TYPE_CONSULAR_CARD => 'Carte consulaire',
    ];

    public const REASON_TOURISM = 'tourisme';

    public const REASON_BUSINESS = 'affaires';

    public const REASON_FAMILY = 'visite_familiale';

    public const REASON_CONFERENCE = 'conference_seminaire';

    public const REASON_HEALTH = 'sante';

    public const REASON_OTHER = 'autre';

    /** @var array<string, string> */
    public const REASON_LABELS = [
        self::REASON_TOURISM => 'Tourisme',
        self::REASON_BUSINESS => 'Affaires',
        self::REASON_FAMILY => 'Visite familiale',
        self::REASON_CONFERENCE => 'Conférence / séminaire',
        self::REASON_HEALTH => 'Santé',
        self::REASON_OTHER => 'Autre',
    ];

    protected $fillable = [
        'reservation_id',
        'travel_type',
        'document_type',
        'document_number',
        'document_scan_front_url',
        'document_scan_back_url',
        'travel_reason',
        'selfie_url',
        'hotel_check_in_date',
        'hotel_check_in_time',
        'hotel_check_out_date',
        'hotel_check_out_time',
        'first_name',
        'last_name',
        'birth_date',
        'birth_place',
        'father_name',
        'mother_name',
        'profession',
        'home_address',
        'children_count',
        'emergency_contact_name',
        'emergency_contact_phone',
        'phone_country_code',
        'phone_number',
        'email',
        'data_confirmed',
        'signature_url',
        'submitted_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'hotel_check_in_date' => 'date',
            'hotel_check_out_date' => 'date',
            'birth_date' => 'date',
            'children_count' => 'integer',
            'data_confirmed' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function labelForTravelType(): string
    {
        return self::TRAVEL_TYPE_LABELS[$this->travel_type] ?? $this->travel_type;
    }

    public function labelForDocumentType(): string
    {
        return self::DOCUMENT_TYPE_LABELS[$this->document_type] ?? $this->document_type;
    }

    public function labelForTravelReason(): string
    {
        return self::REASON_LABELS[$this->travel_reason] ?? $this->travel_reason;
    }
}
