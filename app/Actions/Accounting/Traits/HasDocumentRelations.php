<?php


namespace App\Actions\Accounting\Traits;

use App\Models\Attribute;
use App\Models\Contact;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class HasDocumentRelations
 * @package App\Actions\Accounting\Traits
 *
 * @property int $team_id
 * @property int $category_attribute_id
 * @property int $subcategory_attribute_id
 * @property int $provider_contact_id
 * @property int $receiver_contact_id
 * @property int $author_user_id
 * @property int $paid_by_contact_id
 * @property int $completed_by_user_id
 * @property int $cancelled_by_user_id
 * @property int $updated_by_user_id
 * @property int $deleted_by_user_id
 * @property-read Attribute $category
 * @property-read Attribute $subcategory
 * @property-read Contact $provider
 * @property-read Contact $receiver
 * @property-read User $completedBy
 * @property-read User $cancelledBy
 * @property-read User $updatedBy
 * @property-read User $deletedBy
 * @property-read User $author
 * @property-read Team $team
 */
trait HasDocumentRelations
{
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_contact_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo
     * @internal
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(static::ENTITY_PROVIDER ?? Contact::class, 'provider_contact_id');
    }

    /**
     * @return BelongsTo
     * @internal
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(static::ENTITY_RECEIVER ?? Contact::class, 'receiver_contact_id');
    }

    /**
     * @return BelongsTo
     * @internal
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'category_attribute_id');
    }

    /**
     * @return BelongsTo
     * @internal
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'subcategory_attribute_id');
    }

    /**
     * @return BelongsTo
     * @internal
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
