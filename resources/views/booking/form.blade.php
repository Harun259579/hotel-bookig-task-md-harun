@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Hotel Booking</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('booking.check') }}" method="POST" id="booking-form">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control" min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" required>
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-primary">Check availability</button>
        </div>
    </form>
    <p class="text-muted mt-3">Note: Fridays and Saturdays incur a 20% surcharge. 10% discount applies for 3+ consecutive nights.</p>
</div>

<script>
(async function() {
    const res = await fetch('{{ route('api.disabledDates') }}');
    const disabled = await res.json(); // array of YYYY-MM-DD

    function hasDisabledInRange(from, to) {
        if (!from || !to) return false;
        const a = new Date(from), b = new Date(to);
        for (let d = new Date(a); d < b; d.setDate(d.getDate()+1)) {
            const s = d.toISOString().slice(0,10);
            if (disabled.includes(s)) return true;
        }
        return false;
    }

    const fromEl = document.getElementById('from_date');
    const toEl   = document.getElementById('to_date');
    function validate() {
        if (fromEl.value && toEl.value && hasDisabledInRange(fromEl.value, toEl.value)) {
            alert('Your selected range includes a fully booked date. Please choose different dates.');
            toEl.value = '';
        }
    }
    fromEl.addEventListener('change', validate);
    toEl.addEventListener('change', validate);
})();
</script>
@endsection
