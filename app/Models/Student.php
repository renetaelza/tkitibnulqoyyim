<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id_parents', 'id_registration', 'name', 'birth_place', 'birth_date', 'gender', 'religion', 'group', 'status'])]
class Student extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $primaryKey = 'id_student';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'id_parents', 'id_parents');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'id_student', 'id_class');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'id_student', 'id_student');
    }

    // Scopes for registration payment workflow
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeNonAktif($query)
    {
        return $query->where('status', 'non-aktif');
    }

    // Helper Methods
    public function markAsAktif(): void
    {
        $this->status = 'aktif';
        $this->save();
    }

    public function markAsRejected(): void
    {
        $this->status = 'rejected';
        $this->save();
    }

    
}
