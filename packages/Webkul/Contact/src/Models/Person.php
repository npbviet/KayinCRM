<?php

namespace Webkul\Contact\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Activity\Traits\LogsActivity;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Contact\Contracts\Person as PersonContract;
use Webkul\Contact\Database\Factories\PersonFactory;
use Webkul\Lead\Models\LeadProxy; // Quan trọng để truy cập Leads
use Webkul\Tag\Models\TagProxy;
use Webkul\User\Models\UserProxy;

class Person extends Model implements PersonContract
{
    use CustomAttribute, HasFactory, LogsActivity;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'persons';

    /**
     * Eager loading.
     *
     * @var string
     */
    protected $with = 'organization';

    /**
     * The attributes that are castable.
     *
     * @var array
     */
    protected $casts = [
        'emails'          => 'array',
        'contact_numbers' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'emails',
        'contact_numbers',
        'job_title',
        'user_id',
        'organization_id',
        'unique_id',
    ];

    /**
     * Get the user that owns the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the organization that owns the person.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationProxy::modelClass());
    }

    /**
     * Get the activities.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'person_activities');
    }

    /**
     * The tags that belong to the person.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'person_tags');
    }

    /**
     * Get the leads for the person.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(LeadProxy::modelClass(), 'person_id');
    }

    //---------------------------------------------------------
    // BẮT ĐẦU CÁC ACCESSOR MỚI ĐỂ LẤY DỮ LIỆU TỪ LEADS
    //---------------------------------------------------------

    /**
     * Get the count of completed leads (won leads) for the person.
     *
     * @return int
     */
    public function getCompletedLeadsCountAttribute(): int
    {
        return $this->leads()
            ->whereHas('stage', function ($query) {
                // Giả định Lead Stage code cho 'Thắng' là 'won'
                $query->where('code', 'won');
            })
            ->count();
    }

    /**
     * Get the total value of completed leads (won leads) for the person.
     *
     * @return string
     */
    public function getCompletedLeadsValueAttribute(): string
    {
        $total = $this->leads()
            ->whereHas('stage', function ($query) {
                // Giả định Lead Stage code cho 'Thắng' là 'won'
                $query->where('code', 'won');
            })
            ->sum('lead_value'); // Giả định cột giá trị trong bảng Leads là 'lead_value'
        
        // Trả về giá trị đã định dạng tiền tệ (sử dụng hàm core() của Krayin)
        return core()->formatBasePrice($total);
    }

    //---------------------------------------------------------
    // KẾT THÚC CÁC ACCESSOR MỚI
    //---------------------------------------------------------

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}