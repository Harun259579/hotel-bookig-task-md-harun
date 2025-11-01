# Hotel Booking (Laravel)

**Purpose:**  A simple hotel booking system quickly inside a fresh Laravel app.  
**Stack:** Laravel 9+, PHP 8.2+, MySQL/MariaDB (or SQLite), Bootstrap 5

---

## ✨ Features
- **Room Categories (seeded):**
  - Premium Deluxe – 12,000 BDT
  - Super Deluxe – 10,000 BDT
  - Standard Deluxe – 8,000 BDT
- **Weekend Pricing:** Friday and Saturday add **+20%** to the base price.
- **Long-Stay Discount:** **10% off** the **subtotal** for **3 or more consecutive nights**.
- **Availability Rules:**
  - **3 rooms per category per day.**
  - If a category has 3 bookings on a date → that category is unavailable for that date (**“No room available.”** in UI).
  - If **all categories** are fully booked on a date → that date is disabled via API in the booking form.
- **Validation / Edge Cases:**
  - Valid email (`email` rule).
  - Phone basic regex: `^[0-9+\-\s]{7,15}$`
  - Booking dates cannot be in the past.
  - Price is calculated **per day**, weekend rule applied correctly, long-stay discount applied on the subtotal.
- **Flow:**
  1) Enter Name/Email/Phone, From/To dates  
  2) Check availability → see categories with pricing breakdown  
  3) Choose a category → see final price  
  4) Confirm → **Thank You** page shows full breakdown (Base, Weekend Surcharge, Discount, Final)

---

## 🧰 Prerequisites
- PHP **8.2+**
- Composer
- MySQL/MariaDB (or SQLite)
- Git (optional)

---

## 🚀 Installation (Step-by-step)
> Use this when running as a **new** project.

1) **Create a new Laravel project**
```bash
composer create-project laravel/laravel hotel-booking
cd hotel-booking
```

2) **Copy files from the ZIP**  
Unzip `hotel_booking_addon.zip` and **copy/merge** all files into your Laravel project root (allow overwrite):
```
app/Models/RoomCategory.php
app/Models/Booking.php
app/Models/BookingItem.php
app/Http/Controllers/BookingController.php
database/migrations/*create_room_categories_table.php
database/migrations/*create_bookings_table.php
database/migrations/*create_booking_items_table.php
database/seeders/RoomCategorySeeder.php
resources/views/layouts/app.blade.php
resources/views/booking/form.blade.php
resources/views/booking/select.blade.php
resources/views/booking/thankyou.blade.php
routes/web.php
```

3) **Configure the database**  
Edit `.env` (example – MySQL):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=root
DB_PASSWORD=
```
> Create the `hotel_booking` database in MySQL first.  
> For SQLite: set `DB_CONNECTION=sqlite` and create `database/database.sqlite`.

4) **Register the seeder**  
In `database/seeders/DatabaseSeeder.php`, inside `run()` add:
```php
$this->call(\Database\Seeders\RoomCategorySeeder::class);
```

5) **Run migrations and seeders**
```bash
php artisan migrate --seed
```

6) **Start the local server**
```bash
php artisan serve
```
Open: `http://127.0.0.1:8000/booking`

---

## 🧪 How to Test
1. Fill **Name, Email, Phone** (phone must match `^[0-9+\-\s]{7,15}$`).  
2. Pick **From Date** and **To Date** (past dates are not allowed).  
3. Click **Check availability** → you’ll see 3 categories with pricing breakdowns.  
4. Pick one that is available → check the **Final total** (Base + Weekend surcharge − Discount).  
5. Click **Confirm** → the **Thank You** page shows full details and pricing breakdown.

---

## 🔍 Routes & Endpoints
- `GET /booking` → Booking form  
- `POST /booking/check` → Availability + pricing view  
- `POST /booking/confirm` → Confirm a booking  
- `GET /booking/thank-you/{booking}` → Thank You page  
- `GET /api/disabled-dates` → Dates where **all categories** are fully booked (JSON list)

---

## 🧠 Pricing Logic (in `BookingController`)
- **Weekend (Fri/Sat):** day price = base × 1.20  
- **Base total:** sum of base price across nights  
- **Weekend surcharge:** (weekend-inflated subtotal − base total)  
- **Discount:** if nights ≥ 3 → 10% of subtotal  
- **Final:** subtotal − discount

---

## 🧱 Availability Logic
- `booking_items` stores **one row per night** (category + date).  
- If a date has **count ≥ 3** for a category → that category has no rooms left on that date.  
- If a date has **all categories** at capacity → it becomes a **fully booked date** returned by `/api/disabled-dates` and the form blocks that date range.

---

## 📁 Key Files
- **Models:** `RoomCategory`, `Booking`, `BookingItem`  
- **Controller:** `BookingController` (availability + pricing + booking)  
- **Migrations:** `room_categories`, `bookings`, `booking_items`  
- **Seeder:** `RoomCategorySeeder` (inserts the 3 categories)  
- **Views:** `booking/form`, `booking/select`, `booking/thankyou` (Bootstrap UI)  
- **Routes:** `routes/web.php`

---

## ⚙️ Customization
- Change room quota: `BookingController::ROOMS_PER_CATEGORY_PER_DAY`  
- Change weekend days: adjust `calculatePricing()` checks for `isFriday()` / `isSaturday()`  
- Tighten phone regex: update validation in `check()` / `confirm()`

---

## ❗ Troubleshooting
- **Class "Database\Seeders\RoomCategorySeeder" not found**  
  → Ensure you added the `call()` line in `DatabaseSeeder.php`, then run `composer dump-autoload` and `php artisan migrate --seed`.
- **SQL/DB connection errors**  
  → Verify `.env` credentials and that MySQL is running.
- **404 or view not found**  
  → Ensure routes and view files were copied correctly.

---

## 📜 License
Use and modify this example code as you wish for your projects.
