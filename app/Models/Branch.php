<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Branch extends Model implements Auditable

{
    use HasTranslations, AuditableTrait;
    public $translatable = ['name', 'address'];

    protected $fillable = ['name', 'address', 'location_id', 'phone', 'whatsapp' , 'active', 'lat', 'lng', 'active', 'slug','username','password', 'firebase', 'lang', 'armada_key'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function workingHours()
    {
        return $this->hasMany(BranchWorkingHour::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_branches', 'branch_id', 'product_id')
            ->withPivot('status');
    }

    public function cities()
    {
        return $this->belongsToMany(Location::class, 'branch_cities', 'branch_id', 'city_id')
            ->where('type', 'city');
    }

    /**
     * Check if branch is currently open based on working hours
     */
    public function isCurrentlyOpen()
    {
        $workingHours = $this->workingHours;
        
        // If no working hours defined, consider it always open
        if ($workingHours->isEmpty()) {
            return true;
        }

        $now = now();
        $currentDay = strtolower($now->format('l')); // e.g., 'monday', 'tuesday'
        $currentTime = $now->format('H:i:s');

        // Map day names to match database enum values
        $dayMap = [
            'sunday' => 'sunday',
            'monday' => 'monday',
            'tuesday' => 'tuesday',
            'wednesday' => 'wednesday',
            'thursday' => 'thursday',
            'friday' => 'friday',
            'saturday' => 'saturday'
        ];

        $currentDayEnum = $dayMap[$currentDay] ?? null;
        if (!$currentDayEnum) {
            return false;
        }

        // Check if current day and time fall within any working hour range
        foreach ($workingHours as $wh) {
            $fromDay = strtolower($wh->from_day);
            $toDay = strtolower($wh->to_day);
            
            // Check if current day is within the day range
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $fromIndex = array_search($fromDay, $days);
            $toIndex = array_search($toDay, $days);
            $currentIndex = array_search($currentDayEnum, $days);
            
            if ($fromIndex === false || $toIndex === false || $currentIndex === false) {
                continue;
            }
            
            // Handle day range (e.g., monday to friday)
            $isDayInRange = false;
            if ($fromIndex <= $toIndex) {
                // Normal range (e.g., Monday to Friday)
                $isDayInRange = ($currentIndex >= $fromIndex && $currentIndex <= $toIndex);
            } else {
                // Wrapping range (e.g., Friday to Monday)
                $isDayInRange = ($currentIndex >= $fromIndex || $currentIndex <= $toIndex);
            }
            
            if (!$isDayInRange) {
                continue;
            }
            
            // If day is in range, check time
            if ($wh->from_time && $wh->to_time) {
                $fromTime = $wh->from_time;
                $toTime = $wh->to_time;
                
                // Handle time range that wraps around midnight
                if ($fromTime <= $toTime) {
                    // Normal time range (e.g., 09:00 to 17:00)
                    if ($currentTime >= $fromTime && $currentTime <= $toTime) {
                        return true;
                    }
                } else {
                    // Wrapping time range (e.g., 22:00 to 02:00)
                    if ($currentTime >= $fromTime || $currentTime <= $toTime) {
                        return true;
                    }
                }
            } else {
                // If time is null, consider it open all day for this day range
                return true;
            }
        }

        return false;
    }
}
