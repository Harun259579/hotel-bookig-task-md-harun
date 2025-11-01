@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Select Room Category</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Your Details</h5>
            <p class="mb-1"><strong>Name:</strong> {{ $input['name'] }}</p>
            <p class="mb-1"><strong>Email:</strong> {{ $input['email'] }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $input['phone'] }}</p>
            <p class="mb-1"><strong>Dates:</strong> {{ $input['from_date'] }} → {{ $input['to_date'] }} ({{ $nights }} nights)</p>
        </div>
    </div>

    <form action="{{ route('booking.confirm') }}" method="POST">
        @csrf
        <input type="hidden" name="name" value="{{ $input['name'] }}">
        <input type="hidden" name="email" value="{{ $input['email'] }}">
        <input type="hidden" name="phone" value="{{ $input['phone'] }}">
        <input type="hidden" name="from_date" value="{{ $input['from_date'] }}">
        <input type="hidden" name="to_date" value="{{ $input['to_date'] }}">

        <div class="row g-4">
            @foreach ($categories as $c)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $c['name'] }}</h5>
                        <p class="mb-1"><strong>Base price/night:</strong> {{ number_format($c['base_price']) }} BDT</p>

                        @php
                            $p = $c['pricing'];
                        @endphp
                        <p class="mb-1"><strong>Base total:</strong> {{ number_format($p['base_total']) }} BDT</p>
                        <p class="mb-1"><strong>Weekend surcharge:</strong> +{{ number_format($p['weekend_surcharge']) }} BDT</p>
                        @if ($p['discount'] > 0)
                        <p class="mb-1 text-success"><strong>Discount (3+ nights):</strong> -{{ number_format($p['discount']) }} BDT</p>
                        @endif
                        <h5 class="mt-auto">Final total: {{ number_format($p['final_total']) }} BDT</h5>

                        @if ($c['available'])
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="category_id" id="cat{{ $c['id'] }}" value="{{ $c['id'] }}" required>
                                <label class="form-check-label" for="cat{{ $c['id'] }}">
                                    Choose {{ $c['name'] }}
                                </label>
                            </div>
                        @else
                            <div class="alert alert-warning mt-2">No room available.</div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            <button class="btn btn-success">Confirm Booking</button>
        </div>
    </form>
</div>
@endsection
