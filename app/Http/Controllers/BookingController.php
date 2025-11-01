<?php

namespace App\Http\Controllers;

use App\Models\RoomCategory;
use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    const ROOMS_PER_CATEGORY_PER_DAY = 3;

    public function form()
    {
        return view('booking.form');
    }

    public function disabledDates()
    {
        // Dates where ALL categories are fully booked (>= 3 rooms each)
        $categoriesCount = RoomCategory::count();

        $fullDates = BookingItem::select('date', DB::raw('category_id'), DB::raw('count(*) as cnt'))
            ->groupBy('date','category_id')
            ->get()
            ->groupBy('date')
            ->filter(function($group) {
                // for each date, count how many categories have 3 or more bookings
                $fullCats = $group->filter(fn($row) => $row->cnt >= self::ROOMS_PER_CATEGORY_PER_DAY);
                return $fullCats->count();
            })
            ->map(function($group) use ($categoriesCount) {
                $fullCats = $group->filter(fn($row) => $row->cnt >= self::ROOMS_PER_CATEGORY_PER_DAY)->count();
                return $fullCats >= $categoriesCount;
            })
            ->filter(fn($isFull) => $isFull === true)
            ->keys()
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->values();

        return response()->json($fullDates);
    }

    public function check(Request $request)
    {
        $today = Carbon::today();
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{7,15}$/'],
            'from_date' => ['required', 'date', 'after_or_equal:'.$today->toDateString()],
            'to_date'   => ['required', 'date', 'after:from_date'],
        ], [
            'phone.regex' => 'Phone must be 7-15 digits and may include +, - or spaces.',
        ]);

        $from = Carbon::parse($data['from_date']);
        $to   = Carbon::parse($data['to_date']);
        $nights = $from->diffInDays($to);

        [$disabledDates, $fullyBookedByCategory] = $this->computeFullyBooked($from, $to);
        if (!empty($disabledDates)) {
            return back()->withErrors(['date' => 'Selected range includes fully booked date(s): '.implode(', ', $disabledDates)])->withInput();
        }

        $categories = RoomCategory::all()->map(function($cat) use ($from, $to, $fullyBookedByCategory) {
            $available = $this->isCategoryAvailableForRange($cat->id, $from, $to, $fullyBookedByCategory);
            $pricing = $this->calculatePricing($cat->base_price, $from, $to);
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'base_price' => $cat->base_price,
                'available' => $available,
                'pricing' => $pricing,
            ];
        });

        return view('booking.select', [
            'input' => $data,
            'nights' => $nights,
            'categories' => $categories,
        ]);
    }

    public function confirm(Request $request)
    {
        $today = Carbon::today();
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{7,15}$/'],
            'from_date' => ['required', 'date', 'after_or_equal:'.$today->toDateString()],
            'to_date'   => ['required', 'date', 'after:from_date'],
            'category_id' => ['required', Rule::exists('room_categories','id')],
        ]);

        $from = Carbon::parse($data['from_date']);
        $to   = Carbon::parse($data['to_date']);

        // Final availability check (atomic)
        $categoryId = (int)$data['category_id'];

        return DB::transaction(function() use ($data, $from, $to, $categoryId) {
            // Lock rows for the date range to avoid race conditions
            // (SQLite/MySQL compat: we'll just re-check counts inside transaction)
            $fullyBookedByCategory = $this->getFullyBookedByCategory($from, $to);

            if (!$this->isCategoryAvailableForRange($categoryId, $from, $to, $fullyBookedByCategory)) {
                return back()->withErrors(['category_id' => 'No room available for the selected category and dates.'])->withInput();
            }

            // Calculate pricing
            $baseCategory = RoomCategory::findOrFail($categoryId);
            $pricing = $this->calculatePricing($baseCategory->base_price, $from, $to);
            $nights = $from->diffInDays($to);

            // Create booking
            $booking = Booking::create([
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'category_id' => $categoryId,
                'from_date' => $from->toDateString(),
                'to_date'   => $to->toDateString(),
                'nights'    => $nights,
                'base_total' => $pricing['base_total'],
                'weekend_surcharge' => $pricing['weekend_surcharge'],
                'discount'  => $pricing['discount'],
                'final_total' => $pricing['final_total'],
            ]);

            // Create booking items (one per night)
            $cursor = $from->copy();
            while ($cursor->lt($to)) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'category_id' => $categoryId,
                    'date' => $cursor->toDateString(),
                ]);
                $cursor->addDay();
            }

            return redirect()->route('booking.thankyou', ['booking' => $booking->id]);
        });
    }

    public function thankyou(Booking $booking)
    {
        return view('booking.thankyou', compact('booking'));
    }

    // ----------------- Helpers -----------------

    private function calculatePricing($basePrice, Carbon $from, Carbon $to): array
    {
        $baseTotal = 0;
        $subtotal  = 0;
        $nights = $from->diffInDays($to);

        $cursor = $from->copy();
        while ($cursor->lt($to)) {
            $isWeekend = $cursor->isFriday() || $cursor->isSaturday();
            $dayBase = $basePrice;
            $dayPrice = $isWeekend ? $dayBase * 1.20 : $dayBase;

            $baseTotal += $dayBase;
            $subtotal  += $dayPrice;

            $cursor->addDay();
        }

        $discount = ($nights >= 3) ? round($subtotal * 0.10, 2) : 0;
        $final = round($subtotal - $discount, 2);

        return [
            'base_total' => $baseTotal,
            'weekend_surcharge' => round($subtotal - $baseTotal, 2),
            'discount' => $discount,
            'final_total' => $final,
        ];
    }

    private function computeFullyBooked(Carbon $from, Carbon $to): array
    {
        // Dates fully booked across ALL categories
        $disabled = $this->disabledDates()->getData(true);
        $disabledInRange = [];

        $cursor = $from->copy();
        while ($cursor->lt($to)) {
            $d = $cursor->toDateString();
            if (in_array($d, $disabled)) {
                $disabledInRange[] = $d;
            }
            $cursor->addDay();
        }

        return [$disabledInRange, $this->getFullyBookedByCategory($from, $to)];
    }

    private function getFullyBookedByCategory(Carbon $from, Carbon $to)
    {
        $items = BookingItem::select('date','category_id', DB::raw('count(*) as cnt'))
            ->whereBetween('date', [$from->toDateString(), $to->copy()->subDay()->toDateString()])
            ->groupBy('date','category_id')
            ->get();

        $map = [];
        foreach ($items as $row) {
            $map[$row->date][$row->category_id] = $row->cnt;
        }
        return $map; // [date => [category_id => count]]
    }

    private function isCategoryAvailableForRange(int $categoryId, Carbon $from, Carbon $to, array $fullyBookedByCategory): bool
    {
        $cursor = $from->copy();
        while ($cursor->lt($to)) {
            $d = $cursor->toDateString();
            $cnt = $fullyBookedByCategory[$d][$categoryId] ?? 0;
            if ($cnt >= self::ROOMS_PER_CATEGORY_PER_DAY) {
                return false;
            }
            $cursor->addDay();
        }
        return true;
    }
}
