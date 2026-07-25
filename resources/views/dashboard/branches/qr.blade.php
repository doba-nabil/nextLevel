@extends('dashboard.layout.master')
@section('title', __('admin.qr_code') . ' - ' . $branch->getTranslation('name', app()->getLocale()))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header border-b d-flex justify-content-between align-items-center">
                <span>{{ __('admin.qr_code') }} - {{ $branch->getTranslation('name', app()->getLocale()) }}</span>
                <a href="{{ route('branches.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ti tabler-arrow-left me-1"></i> {{ __('admin.back') ?? 'Back' }}
                </a>
            </h5>
            <div class="card-body text-center mt-5">
                <div class="mb-4 d-flex justify-content-center">
                    <div class="p-3 bg-white rounded-4 shadow-sm border position-relative" style="display: inline-block; width: 300px; height: 300px; padding: 0 !important; overflow: hidden;">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                            ->style('round')
                            ->eye('circle')
                            ->color(184, 205, 53)
                            ->margin(1)
                            ->generate(route('website.scan.qr', $branch->slug ?: $branch->id)) !!}
                        
                        <!-- Overlay Logo -->
                        <div class="position-absolute d-flex align-items-center justify-content-center" style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70px; height: 70px; background: #fff; border-radius: 50%; padding: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                            <img src="{{ asset('website/assets/img/logo.png') }}" alt="Logo" style="width: 100%; height: auto; object-fit: contain;">
                        </div>
                    </div>
                </div>
                <p class="mb-4">{{ __('admin.scan_qr_to_select_branch') ?? 'امسح الكود لفتح الموقع واختيار هذا الفرع للاستلام تلقائياً' }}</p>
                <div class="d-flex justify-content-center gap-3">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="icon-base ti tabler-printer me-1"></i> {{ __('admin.print') ?? 'طباعة' }}
                    </button>
                    <button onclick="downloadQR()" class="btn btn-success">
                        <i class="icon-base ti tabler-download me-1"></i> {{ __('admin.download_as_image') ?? 'تحميل كصورة' }}
                    </button>
                    <button onclick="shareQR()" class="btn btn-info text-white">
                        <i class="icon-base ti tabler-share me-1"></i> {{ __('admin.share') ?? 'مشاركة' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadQR() {
            const svg = document.querySelector('.mb-4 svg');
            if(!svg) return;
            const svgData = new XMLSerializer().serializeToString(svg);
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = function() {
                canvas.width = img.width + 40;
                canvas.height = img.height + 40;
                ctx.fillStyle = "white";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 20, 20);
                
                // Draw the logo over the QR
                const logoImg = new Image();
                logoImg.crossOrigin = "Anonymous";
                logoImg.onload = function() {
                    const circleDiameter = 70;
                    const cx = canvas.width / 2;
                    const cy = canvas.height / 2;
                    
                    // Draw white circle background for logo
                    ctx.beginPath();
                    ctx.arc(cx, cy, circleDiameter / 2, 0, 2 * Math.PI);
                    ctx.fillStyle = 'white';
                    // Optional shadow
                    ctx.shadowColor = 'rgba(0,0,0,0.1)';
                    ctx.shadowBlur = 10;
                    ctx.fill();
                    
                    // Reset shadow for image
                    ctx.shadowColor = 'transparent';
                    ctx.shadowBlur = 0;
                    
                    // Draw image with object-fit: contain
                    const maxImgSize = circleDiameter - 10; // 5px padding on each side
                    let imgW = logoImg.width;
                    let imgH = logoImg.height;
                    const ratio = Math.min(maxImgSize / imgW, maxImgSize / imgH);
                    
                    const finalW = imgW * ratio;
                    const finalH = imgH * ratio;
                    
                    const imgX = cx - (finalW / 2);
                    const imgY = cy - (finalH / 2);
                    
                    ctx.drawImage(logoImg, imgX, imgY, finalW, finalH);
                    
                    const pngFile = canvas.toDataURL('image/png');
                    const downloadLink = document.createElement('a');
                    downloadLink.download = 'QR_{{ $branch->slug ?: $branch->id }}.png';
                    downloadLink.href = pngFile;
                    downloadLink.click();
                };
                logoImg.src = '{{ asset("website/assets/img/logo.png") }}';
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
        }

        function shareQR() {
            const shareUrl = '{{ route("website.scan.qr", $branch->slug ?: $branch->id) }}';
            if (navigator.share) {
                navigator.share({
                    title: '{{ $branch->getTranslation("name", app()->getLocale()) }}',
                    text: '{{ __("admin.scan_to_pickup_from_branch", ["branch" => $branch->getTranslation("name", app()->getLocale())]) }}',
                    url: shareUrl
                }).catch((error) => console.log('Error sharing', error));
            } else {
                navigator.clipboard.writeText(shareUrl).then(() => {
                    alert('{{ __("admin.link_copied_successfully") ?? "تم نسخ الرابط بنجاح" }}');
                });
            }
        }
    </script>
@endsection
