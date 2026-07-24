@extends('website.layout.master')
@section('title', __('website.branches'))
@section('body', 'bg-white')
@section('website-main')
    <!-- CSS Link -->
    <link rel="stylesheet" href="{{ asset('website/assets/css/branches_redesign.css') }}">

    <!-- Content -->
    <div class="breadCrumb_section">
        <div class="container px-lg-0">
            <div class="title_wrapper">
                <h3>{{ __('website.branches') }}</h3>
            </div>
        </div>
    </div>

    <section class="pickup_section">
        <div class="container px-lg-0">
            
            @if(count($allBranches) > 0)
                <!-- Branch Dropdown Selector -->
                <div class="branches_selector_wrapper">
                    <div class="branch_dropdown_label">{{ __('website.select_branch_location') ?? 'SELECT LOCATION' }}</div>
                    
                    <div class="custom_dropdown" id="branchesDropdown">
                        <div class="dropdown_trigger" onclick="toggleBranchDropdown()">
                            <span>{{ $activeBranch->name }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown_options">
                            @foreach($allBranches as $branche)
                                <a href="{{ route('website.branches', $branche->slug) }}" 
                                   class="branch_option {{ $branche->id == $activeBranch->id ? 'active_option' : '' }}">
                                    {{ $branche->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Active Branch Details -->
                <div class="brachCont_Wrap">
                    
                    <div class="row">
                        @if(count($activeBranch->workingHours) > 0)
                        <div class="col-md-6">
                            <div class="info_group">
                                <div class="info_icon"><i class="far fa-clock"></i></div>
                                <div class="info_content">
                                    <h5>{{ __('website.working_hours') }}</h5>
                                    @foreach($activeBranch->workingHours as $wh)
                                        @php
                                            $fromTime = \Carbon\Carbon::parse($wh->from_time)->format('h:i A');
                                            $toTime = \Carbon\Carbon::parse($wh->to_time)->format('h:i A');
                                        @endphp
                                        <p>
                                            <strong>{{ __('admin.' . $wh->from_day) }} - {{ __('admin.' . $wh->to_day) }}:</strong><br>
                                            {{ $fromTime }} - {{ $toTime }}
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <div class="info_group">
                                <div class="info_icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="info_content">
                                    <h5>{{ __('website.contact_information') }}</h5>
                                    
                                    @if($activeBranch->address)
                                        <p style="margin-bottom: 10px;">{{ $activeBranch->address }}</p>
                                    @endif

                                    @if($activeBranch->phone)
                                        @php $cleanPhone = preg_replace('/[^0-9+]/', '', $activeBranch->phone); @endphp
                                        <p><i class="fa fa-phone" style="font-size: 12px; margin-right: 5px;"></i> <a href="tel:{{ $cleanPhone }}">{{ $activeBranch->phone }}</a></p>
                                    @endif
                                    
                                    @if($activeBranch->whatsapp)
                                        @php $cleanWhatsapp = preg_replace('/[^0-9]/', '', $activeBranch->whatsapp); @endphp
                                        <p><i class="fab fa-whatsapp" style="font-size: 12px; margin-right: 5px;"></i> <a href="https://wa.me/{{ $cleanWhatsapp }}" target="_blank">{{ $activeBranch->whatsapp }}</a></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($activeBranch->lat)
                        <div class="map_frame_wrapper">
                            <iframe
                                width="100%" height="100%" style="border:0;" allowfullscreen=""
                                loading="lazy"
                                src="https://www.google.com/maps?q={{ $activeBranch->lat }},{{ $activeBranch->lng }}&hl=ar&z=15&output=embed">
                            </iframe>
                        </div>
                    @endif
                </div>

            @else
                <div class="col-12">
                    <div class="alert alert-warning border-0 rounded-0 text-center p-5" style="background: #fffdf0; border-left: 4px solid #f6d814 !important;">
                        <h4 style="font-family: var(--artisanal-font); color: #333;">{{ __('website.no_branches_country') }}</h4>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <script>
        function toggleBranchDropdown() {
            document.getElementById('branchesDropdown').classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown_trigger') && !event.target.matches('.dropdown_trigger *')) {
                var dropdowns = document.getElementsByClassName("custom_dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('active')) {
                        openDropdown.classList.remove('active');
                    }
                }
            }
        }
    </script>

    <!--/ Content -->
@endsection
