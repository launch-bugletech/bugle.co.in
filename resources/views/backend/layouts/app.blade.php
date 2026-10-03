<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="vapid-public-key" content="{{ config('app.vapid_public_key', '') }}">
	<title>Bugle Technologies</title>
	<link rel="icon" href="{{asset('public/favicon_bugle.png')}}" type="image/x-icon">
	@stack('styles')
	<link rel="stylesheet" href="{{ asset('backend/ebazar.style.min.css') }}">
	<link rel="stylesheet" href="{{ asset('backend/custom.css') }}">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icofont/4.5.0/css/icofont.min.css">
	<style type="text/css">
		.flash-message-container {
			position: fixed;
			top: 185px;
			right: 40px;
			z-index: 9999;
		}

		@media only screen and (max-width: 991px) {
			.sidebar.open {
				left: 0;
				top: 15px
			}
		}

		.flash-message {
			display: inline-block;
			background-color: #d1e7dd;
			color: #0f5132;
			padding: 12px 20px;
			border-radius: 4px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			opacity: 0;
			transform: translateX(100%);
			transition: opacity 0.3s ease, transform 0.5s ease;
		}

		.sidebar .menu-list {
			flex-grow: 1;
		}

		.flash-message-error {
			display: inline-block;
			background-color: #f5c2c7;
			color: #f5c2c7;
			padding: 12px 20px;
			border-radius: 4px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			opacity: 0;
			transform: translateX(100%);
			transition: opacity 0.3s ease, transform 0.5s ease;
		}

		@media (max-width: 767px) {
			.navbar {
				margin-bottom: 40px;
			}

			.avatar.lg {
				min-width: 38px;
				width: 30px;
				height: 40px;
			}
		}

		/* ── Notification Bell ── */
		.notif-wrap {
			position: relative;
			display: inline-flex;
			align-items: center;
		}

		.notif-btn {
			background: none;
			border: none;
			cursor: pointer;
			padding: 6px 8px;
			border-radius: 6px;
			color: #555;
			position: relative;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.notif-btn:hover {
			background: rgba(0, 0, 0, 0.05);
		}

		.notif-badge {
			position: absolute;
			top: 0px;
			right: 0px;
			background: #e74c3c;
			color: #fff;
			font-size: 10px;
			font-weight: 600;
			min-width: 16px;
			height: 16px;
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 0 3px;
			pointer-events: none;
			line-height: 1;
		}

		.notif-dropdown {
			display: none;
			position: absolute;
			top: calc(100% + 8px);
			right: 0;
			width: 340px;
			background: #fff;
			border: 1px solid #e0e0e0;
			border-radius: 10px;
			box-shadow: 0 6px 24px rgba(0, 0, 0, 0.13);
			z-index: 99999;
			overflow: hidden;
		}

		.notif-dropdown.open {
			display: block;
		}

		.notif-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 12px 16px;
			border-bottom: 1px solid #f0f0f0;
		}

		.notif-header-title {
			font-size: 14px;
			font-weight: 600;
			color: #222;
			margin: 0;
		}

		.notif-header-title span {
			font-weight: 400;
			color: #999;
			font-size: 13px;
		}

		.notif-mark-all {
			font-size: 12px;
			color: #3498db;
			background: none;
			border: none;
			cursor: pointer;
			padding: 0;
			text-decoration: none;
		}

		.notif-mark-all:hover {
			text-decoration: underline;
		}

		.notif-list {
			max-height: 320px;
			overflow-y: auto;
		}

		.notif-item {
			display: flex;
			gap: 10px;
			padding: 12px 16px;
			border-bottom: 1px solid #f5f5f5;
			cursor: pointer;
			transition: background 0.15s;
			background: #fff;
		}

		.notif-item:last-child {
			border-bottom: none;
		}

		.notif-item:hover {
			background: #f8f9fa;
		}

		.notif-item.unread {
			background: #f0f7ff;
		}

		.notif-item.unread:hover {
			background: #e8f2fc;
		}

		.notif-icon {
			width: 36px;
			height: 36px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			font-size: 15px;
		}

		.notif-icon.type-leave {
			background: #fef3c7;
			color: #92400e;
		}

		.notif-icon.type-task {
			background: #dbeafe;
			color: #1d4ed8;
		}

		.notif-body {
			flex: 1;
			min-width: 0;
		}

		.notif-msg {
			font-size: 13px;
			color: #222;
			line-height: 1.4;
			margin: 0 0 3px;
			word-break: break-word;
		}

		.notif-time {
			font-size: 11px;
			color: #999;
		}

		.notif-dot {
			width: 7px;
			height: 7px;
			border-radius: 50%;
			background: #3498db;
			flex-shrink: 0;
			margin-top: 5px;
		}

		.notif-empty {
			padding: 28px 16px;
			text-align: center;
			color: #aaa;
			font-size: 13px;
		}

		.notif-footer {
			padding: 10px 16px;
			text-align: center;
			border-top: 1px solid #f0f0f0;
		}

		.notif-footer a {
			font-size: 13px;
			color: #3498db;
			text-decoration: none;
		}

		.notif-footer a:hover {
			text-decoration: underline;
		}
	</style>
	@stack('custom_styles')
</head>

<body>
	<!-- Body: Header -->
	<div class="header">
		<nav class="navbar py-4" style="background-color: #FBFAF6; height: 72px;">
			<div class="container-xxl">

				<!-- header rightbar icon -->
				<div class="h-right d-flex align-items-center mr-5 mr-lg-0 order-1">

					<div class="d-flex">
						<a class="nav-link collapsed font-weight-bold" href="{{url('contact-us')}}" title="Get Help"
							style="color: #101010;">
							Contact
						</a>
					</div>

					<div class="d-flex">
						<a class="nav-link collapsed font-weight-bold" href="{{url('about-us')}}" title="Get Help"
							style="color: #101010;">
							About Us
						</a>
					</div>

					<!-- ── Notification Bell Popup ── -->
					@php
						$unreadNotifs = auth()->user()->unreadNotifications->count();
					@endphp
					<div class="d-flex align-items-center me-2">
						<div class="notif-wrap">

							<!-- Bell Button -->
							<button class="notif-btn" id="notifToggle" aria-label="Notifications" title="Notifications">
								<i class="fa fa-bell" style="font-size: 18px;"></i>
								@if($unreadNotifs > 0)
									<span class="notif-badge" id="notifBadge">
										{{ $unreadNotifs > 99 ? '99+' : $unreadNotifs }}
									</span>
								@else
									<span class="notif-badge" id="notifBadge" style="display:none;">0</span>
								@endif
							</button>

							<!-- Dropdown Panel -->
							<div class="notif-dropdown" id="notifDropdown">

								<div class="notif-header">
									<p class="notif-header-title">
										Notifications
										<span id="notifUnreadLabel">
											@if($unreadNotifs > 0)
												({{ $unreadNotifs }} unread)
											@else
												(all read)
											@endif
										</span>
									</p>
									<button class="notif-mark-all" id="markAllBtn" onclick="notifMarkAll()">
										Mark all read
									</button>
								</div>

								<div class="notif-list" id="notifList">
									@php
										$notifications = auth()->user()->notifications()->latest()->take(20)->get();
									@endphp

									@forelse($notifications as $notif)
										<div class="notif-item {{ $notif->read_at ? '' : 'unread' }}"
											data-id="{{ $notif->id }}"
											data-type="{{ $notif->data['type'] ?? '' }}"
											data-ref="{{ $notif->data['leave_id'] ?? ($notif->data['task_id'] ?? '') }}"
											onclick="notifRead(this)">

											<div class="notif-icon type-{{ $notif->data['type'] ?? 'task' }}">
												@if(($notif->data['type'] ?? '') === 'leave')
													<i class="fa fa-calendar"></i>
												@else
													<i class="fa fa-tasks"></i>
												@endif
											</div>

											<div class="notif-body">
												<p class="notif-msg">{{ $notif->data['message'] ?? '' }}</p>
												<span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span>
											</div>

											@if(!$notif->read_at)
												<div class="notif-dot"></div>
											@endif
										</div>
									@empty
										<div class="notif-empty">
											<i class="fa fa-bell-slash" style="font-size:24px; display:block; margin-bottom:8px; color:#ccc;"></i>
											No notifications yet
										</div>
									@endforelse
								</div>

								<div class="notif-footer">
									<a href="{{ route('admin.notifications') }}">View all notifications</a>
								</div>
							</div>
							<!-- /Dropdown Panel -->

						</div>
					</div>
					<!-- ── /Notification Bell Popup ── -->

					<div class="dropdown zindex-popover">
						<div class="dropdown-menu rounded-lg shadow border-0 dropdown-animation dropdown-menu-md-end p-0 m-0 mt-3">
							<div class="card border-0">
								<ul class="list-unstyled py-2 px-3">
									<li><a href="#" class=""><img src="{!! backendAssets('dist/assets/images/flag/GB.png') !!}" alt=""> English</a></li>
									<li><a href="#" class=""><img src="{!! backendAssets('dist/assets/images/flag/DE.png') !!}" alt=""> German</a></li>
									<li><a href="#" class=""><img src="{!! backendAssets('dist/assets/images/flag/FR.png') !!}" alt=""> French</a></li>
									<li><a href="#" class=""><img src="{!! backendAssets('dist/assets/images/flag/IT.png') !!}" alt=""> Italian</a></li>
									<li><a href="#" class=""><img src="{!! backendAssets('dist/assets/images/flag/RU.png') !!}" alt=""> Russian</a></li>
								</ul>
							</div>
						</div>
					</div>

					<!-- Clock In / Clock Out -->
					<div class="mt-3 p-2 border-top">
						@php
							$lastAttendance = \App\Models\Attendance::where('user_id', Auth::id())->latest()->first();
							$clockedIn = $lastAttendance && !$lastAttendance->clock_out_at;
						@endphp
						<div id="attendance-actions">
							@if(!$clockedIn)
								<button class="btn btn-success btn-sm w-100" data-bs-toggle="modal"
									data-bs-target="#clockInModal" style="margin-top: -21px; margin-right: 30px;">
									Clock In
								</button>
							@else
								<button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal"
									data-bs-target="#clockOutModal" style="margin-top: -21px; margin-right: 30px;">
									Clock Out
								</button>
							@endif
						</div>
					</div>

					<!-- Clock In Modal -->
					<div class="modal fade" id="clockInModal" tabindex="-1" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Clock In</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
								</div>
								<div class="modal-body">
									<video id="clockin-video" autoplay playsinline style="width:100%; border-radius:5px;"></video>
									<canvas id="clockin-canvas" style="display:none;"></canvas>
									<div class="mt-2 text-center">
										<button id="clockin-capture" class="btn btn-primary btn-sm">Capture</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Clock Out Modal -->
					<div class="modal fade" id="clockOutModal" tabindex="-1" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Clock Out</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
								</div>
								<div class="modal-body">
									<video id="clockout-video" autoplay playsinline style="width:100%; border-radius:5px;"></video>
									<canvas id="clockout-canvas" style="display:none;"></canvas>
									<div class="mt-2 text-center">
										<button id="clockout-capture" class="btn btn-primary btn-sm">Capture</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					@if(Auth::user() != "")
						@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3)
							<div class="dropdown user-profile ml-2 ml-sm-3 d-flex align-items-center zindex-popover">
								<div class="u-info me-2">
									<p class="mb-0 text-end line-height-sm"><span class="font-weight-bold">{{Auth::user()->username}}</span></p>
								</div>
								<a class="nav-link dropdown-toggle pulse p-0" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static">
									<img class="avatar lg rounded-circle img-thumbnail" src="{!! backendAssets('dist/assets/images/profile_av.svg') !!}" alt="profile">
								</a>
								<div class="dropdown-menu rounded-lg shadow border-0 dropdown-animation dropdown-menu-end p-0 m-0">
									<div class="card border-0 w280">
										<div class="card-body pb-0">
											<div class="d-flex py-1">
												<img class="avatar rounded-circle" src="{!! backendAssets('dist/assets/images/profile_av.svg') !!}" alt="profile">
												<div class="flex-fill ms-3">
													<p class="mb-0"><span class="font-weight-bold">{{Auth::user()->name}}</span></p>
													<small class="">{{Auth::user()->email}}</small>
												</div>
											</div>
											<div><hr class="dropdown-divider border-dark"></div>
										</div>
										<div class="list-group m-2">
											<a href="{{url('admin/edit_profile')}}" class="list-group-item list-group-item-action border-0"><i class="icofont-ui-user fs-5 me-3"></i>Profile Page</a>
											<a href="{{url('admin/reset_password')}}" class="list-group-item list-group-item-action border-0"><i class="icofont-ui-lock fs-5 me-3"></i>Change Password</a>
											<a href="{{url('/logout')}}" class="list-group-item list-group-item-action border-0"><i class="icofont-logout fs-5 me-3"></i>Signout</a>
										</div>
									</div>
								</div>
							</div>
						@else
							@php $customer = DB::table('customer')->where('user_id', Auth::user()->id)->first(); @endphp
							<div class="dropdown user-profile ml-2 ml-sm-3 d-flex align-items-center zindex-popover">
								<div class="u-info me-2">
									<p class="mb-0 text-end line-height-sm"><span class="font-weight-bold">{{Auth::user()->name}}</span></p>
									<small>Customer Profile</small>
								</div>
								<a class="nav-link dropdown-toggle pulse p-0" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static">
									<img class="avatar lg rounded-circle img-thumbnail" src='{{asset("public/img/companyLogo/default-logo.jpg")}}' alt="profile" />
								</a>
								<div class="dropdown-menu rounded-lg shadow border-0 dropdown-animation dropdown-menu-end p-0 m-0">
									<div class="card border-0 w280">
										<div class="card-body pb-0">
											<div class="d-flex py-1">
												<img class="avatar lg rounded-circle img-thumbnail" src='{{asset("public/img/companyLogo/default-logo.jpg")}}' alt="profile" />
												<div class="flex-fill ms-3">
													<p class="mb-0"><span class="font-weight-bold">{{Auth::user()->name}}</span></p>
													<small class="">{{Auth::user()->email}}</small>
												</div>
											</div>
											<div><hr class="dropdown-divider border-dark"></div>
										</div>
										<div class="list-group m-2">
											<a href="{{url('/customer_edit_profile/edit')}}" class="list-group-item list-group-item-action border-0"><i class="icofont-ui-user fs-5 me-3"></i>Profile Page</a>
											<a href="{{url('/logout')}}" class="list-group-item list-group-item-action border-0"><i class="icofont-logout fs-5 me-3"></i>Signout</a>
										</div>
									</div>
								</div>
							</div>
						@endif
					@else
						<div class="setting ms-2">
							<h6 style="margin-right:10px;"><a href="{{url('/login')}}"><i class="icofont-login fs-5 me-3" style="margin-right:0px;"></i>Login</a></h6>
						</div>
					@endif

				</div>

				<!-- menu toggler -->
				<button class="navbar-toggler p-0 border-0 menu-toggle order-3" type="button" data-bs-toggle="collapse" data-bs-target="#mainHeader">
					<span class="fa fa-bars"></span>
				</button>

				<!-- main menu Search-->
				<a href="{!! backendRoutePut('home') !!}" class="mb-0 brand-icon">
					<span class="logo-icon">
						<img src="{{asset('Bugle_logo.png')}}" height="60" style="width:210px; height: 63px; margin-left: -35px;">
					</span>
				</a>

			</div>
		</nav>
	</div>

	<div id="ebazar-layout" class="theme-blue">
		@if(Auth::user() != "" && Auth::user()->role_id == 1 || Auth::user()->role_id == 3)
			@include(backendView('includes.sidebar'))
		@else
			@include(backendView('includes.customer_sidebar'))
		@endif

		<!-- main body area -->
		<div class="main px-lg-4 px-md-4">
			<div class="body d-flex py-3">
				@yield('content')
			</div>
			@yield('footer')
			@stack('modals')
		</div>
	</div>

	<!-- Jquery Core Js -->
	<script src="{!! backendAssets('dist/assets/bundles/libscripts.bundle.js') !!}"></script>

	@stack('scripts')

	<script>
		function showFlashMessage() {
			var flashMessage = document.querySelector('.flash-message');
			flashMessage.style.opacity = '1';
			flashMessage.style.transform = 'translateX(0)';
			setTimeout(function () {
				flashMessage.style.opacity = '0';
				flashMessage.style.transform = 'translateX(100%)';
			}, 3000);
		}
		document.addEventListener('DOMContentLoaded', function () {
			showFlashMessage();
		});
	</script>

	<script src="{!! backendAssets('dist/assets/js/template.js') !!}"></script>
	<script src="{{ asset('asset/plugin/select2/js/select2.full.min.js')}}"></script>
	<script>
		$(document).ready(function () {
			$('.js-example-basic-multiple').select2();
		});
	</script>

	<script>
		window.onunload = function () { };
		if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
			location.reload();
		}
	</script>

	<script>
		function showSuccessMessage(message) {
			const alert = document.createElement('div');
			alert.className = 'alert alert-success position-fixed top-0 end-0 m-4';
			alert.style.zIndex = 9999;
			alert.innerText = message;
			document.body.appendChild(alert);
			setTimeout(() => { alert.remove(); }, 3000);
		}
	</script>

	<script>
		const clockInUrl  = "{{ route('attendance.clockin') }}";
		const clockOutUrl = "{{ route('attendance.clockout') }}";

		document.addEventListener('DOMContentLoaded', function () {
			let currentStream = null;
			let isProcessing  = false;

			const clockinVideo    = document.getElementById('clockin-video');
			const clockinCanvas   = document.getElementById('clockin-canvas');
			const clockinCapture  = document.getElementById('clockin-capture');
			const clockoutVideo   = document.getElementById('clockout-video');
			const clockoutCanvas  = document.getElementById('clockout-canvas');
			const clockoutCapture = document.getElementById('clockout-capture');

			function startCamera(video) {
				navigator.mediaDevices.getUserMedia({ video: true, audio: false })
					.then(stream => { currentStream = stream; video.srcObject = stream; })
					.catch(() => alert('Camera access denied'));
			}

			function stopCamera(video) {
				if (currentStream) { currentStream.getTracks().forEach(t => t.stop()); currentStream = null; }
				video.srcObject = null;
			}

			function captureAndSend(video, canvas, url, successCallback, errorCallback) {
				if (isProcessing) return;
				isProcessing = true;
				canvas.width  = video.videoWidth;
				canvas.height = video.videoHeight;
				canvas.getContext('2d').drawImage(video, 0, 0);
				const imgData = canvas.toDataURL('image/jpeg', 0.9);
				navigator.geolocation.getCurrentPosition(position => {
					fetch(url, {
						method: 'POST',
						headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
						body: JSON.stringify({ photo: imgData, lat: position.coords.latitude, lng: position.coords.longitude })
					})
					.then(res => res.json())
					.then(data => {
						isProcessing = false;
						if (data.success) { showSuccessMessage(data.message); successCallback(); }
						else { alert(data.message); errorCallback(); }
					})
					.catch(() => { isProcessing = false; alert('Something went wrong'); errorCallback(); });
				}, () => { isProcessing = false; alert('Location permission required'); errorCallback(); });
			}

			document.getElementById('clockInModal').addEventListener('shown.bs.modal', () => { startCamera(clockinVideo); clockinCapture.disabled = false; clockinCapture.innerText = 'Capture'; });
			document.getElementById('clockInModal').addEventListener('hidden.bs.modal', () => { stopCamera(clockinVideo); });
			clockinCapture.addEventListener('click', () => {
				clockinCapture.disabled = true; clockinCapture.innerText = 'Processing...';
				captureAndSend(clockinVideo, clockinCanvas, clockInUrl,
					() => { bootstrap.Modal.getInstance(document.getElementById('clockInModal')).hide(); document.getElementById('attendance-actions').innerHTML = `<button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#clockOutModal">Clock Out</button>`; },
					() => { clockinCapture.disabled = false; clockinCapture.innerText = 'Capture'; }
				);
			});

			document.getElementById('clockOutModal').addEventListener('shown.bs.modal', () => { startCamera(clockoutVideo); clockoutCapture.disabled = false; clockoutCapture.innerText = 'Capture'; });
			document.getElementById('clockOutModal').addEventListener('hidden.bs.modal', () => { stopCamera(clockoutVideo); });
			clockoutCapture.addEventListener('click', () => {
				clockoutCapture.disabled = true; clockoutCapture.innerText = 'Processing...';
				captureAndSend(clockoutVideo, clockoutCanvas, clockOutUrl,
					() => { bootstrap.Modal.getInstance(document.getElementById('clockOutModal')).hide(); document.getElementById('attendance-actions').innerHTML = `<button class="btn btn-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#clockInModal">Clock In</button>`; },
					() => { clockoutCapture.disabled = false; clockoutCapture.innerText = 'Capture'; }
				);
			});
		});
	</script>

	<!-- ── Notification Bell JS ── -->
	<script>
		const _notifMarkAllUrl = "{{ route('admin.notifications.read') }}";
		const _csrfToken       = document.querySelector('meta[name="csrf-token"]').content;

		/* Toggle open / close */
		document.getElementById('notifToggle').addEventListener('click', function (e) {
			e.stopPropagation();
			document.getElementById('notifDropdown').classList.toggle('open');
		});

		/* Close when clicking outside */
		document.addEventListener('click', function (e) {
			const wrap = document.querySelector('.notif-wrap');
			if (wrap && !wrap.contains(e.target)) {
				document.getElementById('notifDropdown').classList.remove('open');
			}
		});

		/* Update badge count */
		function notifUpdateBadge(count) {
			const badge = document.getElementById('notifBadge');
			const label = document.getElementById('notifUnreadLabel');
			count = Math.max(0, count);
			if (count === 0) {
				badge.style.display = 'none';
				label.textContent   = '(all read)';
			} else {
				badge.style.display = 'flex';
				badge.textContent   = count > 99 ? '99+' : count;
				label.textContent   = '(' + count + ' unread)';
			}
		}

		/* Mark a single notification as read on click */
		function notifRead(el) {
			const id   = el.dataset.id;
			const type = el.dataset.type;
			const ref  = el.dataset.ref;

			if (el.classList.contains('unread')) {
				fetch(_notifMarkAllUrl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
					body: JSON.stringify({ id: id })
				}).then(() => {
					el.classList.remove('unread');
					const dot = el.querySelector('.notif-dot');
					if (dot) dot.remove();
					const currentCount = parseInt(document.getElementById('notifBadge').textContent) || 0;
					notifUpdateBadge(currentCount - 1);
				});
			}

			/* Redirect based on notification type */
			if (type === 'leave' && ref) {
				window.location.href = '/admin/leaves/' + ref;
			} else if (type === 'task' && ref) {
				window.location.href = '/admin/tasks/board';
			}
		}

		/* Mark ALL as read */
		function notifMarkAll() {
			fetch(_notifMarkAllUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
				body: JSON.stringify({ mark_all: true })
			}).then(res => res.json()).then(() => {
				document.querySelectorAll('#notifList .notif-item.unread').forEach(function (el) {
					el.classList.remove('unread');
					const dot = el.querySelector('.notif-dot');
					if (dot) dot.remove();
				});
				notifUpdateBadge(0);
			});
		}
	</script>
	<!-- ── /Notification Bell JS ── -->

	<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1/dist/echo.iife.js"></script>
	<script src="{{ asset('js/push-notifications.js') }}"></script>

	@stack('custom_scripts')
</body>

</html>