@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="alert alert-success">
        <h4 class="alert-heading">Thank you! Your booking is confirmed.</h4>
        <p>We've sent a confirmation to <strong>{{ $booking->email }}</strong>. Our team will contact you at <strong>{{ $booking->phone }}</strong> with next steps.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Booking Details</h5>
            <p class="mb-1"><strong>Name:</strong> {{ $booking->name }}</p>
            <p class="mb-1"><strong>Category:</strong> {{ $booking->category->name }}</p>
            <p class="mb-1"><strong>From:</strong> {{ $booking->from_date->toDateString() }}</p>
            <p class="mb-1"><strong>To:</strong> {{ $booking->to_date->toDateString() }}</p>
            <p class="mb-1"><strong>Nights:</strong> {{ $booking->nights }}</p>
            <hr>
            <p class="mb-1"><strong>Base price total:</strong> {{ number_format($booking->base_total) }} BDT</p>
            <p class="mb-1"><strong>Weekend surcharge:</strong> +{{ number_format($booking->weekend_surcharge) }} BDT</p>
            @if ($booking->discount > 0)
            <p class="mb-1 text-success"><strong>Discount:</strong> -{{ number_format($booking->discount) }} BDT</p>
            @endif
            <h4 class="mt-2">Final price: {{ number_format($booking->final_total) }} BDT</h4>
        </div>
    </div>

    <a href="{{ route('booking.form') }}" class="btn btn-link mt-3">Make another booking</a>
</div>
@endsection
