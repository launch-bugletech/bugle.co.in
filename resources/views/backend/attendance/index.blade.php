@extends(backendView('layouts.app'))

@section('title', 'Attendance')

@php
    $pagename = $pagename ?? 'attendance';
@endphp

@section('content')
<div class="container">
    <h3>Attendance</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 3)
        <div class="row">
            <div class="col-md-6">
                <div id="camera-area">
                    <video id="video" width="100%" autoplay playsinline></video>
                    <canvas id="canvas" style="display:none;"></canvas>

                    <div class="mt-2">
                        <button id="capture" class="btn btn-primary">Capture Photo</button>
                        <button id="retake" class="btn btn-secondary" style="display:none">Retake</button>
                    </div>

                    <div class="mt-3" id="preview" style="display:none;">
                        <img id="preview-img" style="max-width:100%; border-radius:8px;" />
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <p>Last status:</p>
                @if($last && $last->clock_in_at)
                    <p>Clocked in at: {{ $last->clock_in_at }}
                    @if($last->clock_out_at)
                        <br>Clocked out at: {{ $last->clock_out_at }}
                    @else
                        <br><strong>Not yet clocked out</strong>
                    @endif
                    </p>
                @else
                    <p>No record found</p>
                @endif

                <form id="clockin-form" method="POST" action="{{ route('attendance.clockin') }}">
                    @csrf
                    <input type="hidden" name="photo" id="clockin-photo">
                    <input type="hidden" name="lat" id="clockin-lat">
                    <input type="hidden" name="lng" id="clockin-lng">
                    <button id="clockin-btn" class="btn btn-success mt-2" disabled>Clock In</button>
                </form>

                <form id="clockout-form" method="POST" action="{{ route('attendance.clockout') }}">
                    @csrf
                    <input type="hidden" name="photo" id="clockout-photo">
                    <input type="hidden" name="lat" id="clockout-lat">
                    <input type="hidden" name="lng" id="clockout-lng">
                    <button id="clockout-btn" class="btn btn-danger mt-2" disabled>Clock Out</button>
                </form>
            </div>
        </div>
    @else
        <p>Clock In/Out is not available for admins.</p>
    @endif
</div>
@endsection

@section('scripts')
@if(Auth::user()->role_id != 1 && Auth::user()->role_id != 3)
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('capture');
    const retakeBtn = document.getElementById('retake');
    const preview = document.getElementById('preview');
    const previewImg = document.getElementById('preview-img');

    const clockinPhoto = document.getElementById('clockin-photo');
    const clockoutPhoto = document.getElementById('clockout-photo');
    const clockinBtn = document.getElementById('clockin-btn');
    const clockoutBtn = document.getElementById('clockout-btn');

    // Start camera
    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(stream => { video.srcObject = stream; })
        .catch(err => alert('Camera access denied.'));

    // Capture photo
    captureBtn.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const imgData = canvas.toDataURL('image/jpeg', 0.9);

        previewImg.src = imgData;
        preview.style.display = 'block';
        retakeBtn.style.display = 'inline-block';
        captureBtn.style.display = 'none';

        if(clockinPhoto) clockinPhoto.value = imgData;
        if(clockoutPhoto) clockoutPhoto.value = imgData;

        if(clockinBtn) clockinBtn.disabled = false;
        if(clockoutBtn) clockoutBtn.disabled = false;
    });

    // Retake photo
    retakeBtn.addEventListener('click', () => {
        preview.style.display = 'none';
        previewImg.src = '';
        retakeBtn.style.display = 'none';
        captureBtn.style.display = 'inline-block';

        if(clockinBtn) clockinBtn.disabled = true;
        if(clockoutBtn) clockoutBtn.disabled = true;
    });

    // Get geolocation
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            if(clockinPhoto) {
                document.getElementById('clockin-lat').value = pos.coords.latitude;
                document.getElementById('clockin-lng').value = pos.coords.longitude;
            }
            if(clockoutPhoto) {
                document.getElementById('clockout-lat').value = pos.coords.latitude;
                document.getElementById('clockout-lng').value = pos.coords.longitude;
            }
        });
    }
});
</script>
@endif
@endsection
