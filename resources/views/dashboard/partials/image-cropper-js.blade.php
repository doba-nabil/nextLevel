<script>
    'use strict';
    
    // Global cropper instance
    let cropperInstance = null;
    let currentDropzone = null;
    let currentFile = null;
    let currentInputName = null;
    let isCropping = false; // Flag to prevent re-triggering cropper
    
    // Initialize image cropper functionality
    function initImageCropper(dropzoneSelector, inputName, cropAspectRatio = null, currentImageUrl = null) {
        Dropzone.autoDiscover = false;
        
        const dropzoneEl = document.querySelector(dropzoneSelector);
        if (!dropzoneEl) return null;
        
        // Destroy any existing Dropzone instance on this element
        if (dropzoneEl.dropzone) {
            dropzoneEl.dropzone.destroy();
        }
        
        // Also check Dropzone.instances array
        const existingInstance = Dropzone.instances.find(dz => dz.element === dropzoneEl);
        if (existingInstance) {
            existingInstance.destroy();
        }
        
        const myDropzone = new Dropzone(dropzoneEl, {
            url: "#",
            autoProcessQueue: false,
            uploadMultiple: false,
            maxFiles: 1,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here or click to upload"
        });
        
        // Show existing image if provided
        if (currentImageUrl) {
            let mockFile = { name: "Current Image", size: 100 };
            myDropzone.emit("addedfile", mockFile);
            myDropzone.emit("thumbnail", mockFile, currentImageUrl);
            myDropzone.emit("complete", mockFile);
            myDropzone.files.push(mockFile);
            
            const previewImg = dropzoneEl.querySelector('.dz-preview img');
            if (previewImg) {
                previewImg.style.width = '100%';
                previewImg.style.height = 'auto';
                previewImg.style.objectFit = 'contain';
            }
        }
        
        myDropzone.on("addedfile", function(file) {
            // Skip if we're in the middle of cropping
            if (isCropping) {
                isCropping = false;
                return;
            }
            
            // Skip if it's the existing image mock file
            if (file.name === "Current Image") {
                return;
            }
            
            // Remove previous file if exists (except current image)
            if (myDropzone.files.length > 1) {
                const filesToRemove = myDropzone.files.filter(f => f.name !== "Current Image");
                filesToRemove.forEach(f => myDropzone.removeFile(f));
            }
            
            // Read the file and show cropper modal
            const reader = new FileReader();
            reader.onload = function(e) {
                currentDropzone = myDropzone;
                currentFile = file;
                currentInputName = inputName;
                isCropping = true;
                
                // Set image source and show modal
                const cropperImage = document.getElementById('cropperImage');
                cropperImage.src = e.target.result;
                
                // Reset crop shape to square
                document.getElementById('cropSquare').checked = true;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('imageCropperModal'));
                modal.show();
                
                // Initialize cropper after modal is shown
                setTimeout(() => {
                    initCropper(cropperImage, cropAspectRatio);
                }, 300);
            };
            reader.readAsDataURL(file);
        });
        
        myDropzone.on("removedfile", function(file) {
            // If removing the current image, clear the input
            if (file.name === "Current Image" || !file.name) {
                const inputFile = document.querySelector(`input[name='${inputName}']`);
                if (inputFile) {
                    inputFile.value = "";
                }
            }
        });
        
        return myDropzone;
    }
    
    // Initialize cropper
    function initCropper(imageElement, aspectRatio = null) {
        // Destroy existing cropper if any
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        
        const cropperOptions = {
            viewMode: 1,
            aspectRatio: aspectRatio || NaN, // NaN means free aspect ratio
            autoCropArea: 0.8,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        };
        
        cropperInstance = new Cropper(imageElement, cropperOptions);
        
        // Handle crop shape changes
        document.querySelectorAll('input[name="cropShape"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (cropperInstance) {
                    const shape = this.value;
                    if (shape === 'square') {
                        cropperInstance.setAspectRatio(1);
                    } else if (shape === 'circle') {
                        cropperInstance.setAspectRatio(1);
                    } else {
                        cropperInstance.setAspectRatio(NaN);
                    }
                }
            });
        });
    }
    
    // Handle crop button click
    document.getElementById('cropImageBtn')?.addEventListener('click', function() {
        if (!cropperInstance) return;
        
        const canvas = cropperInstance.getCroppedCanvas({
            width: 800,
            height: 800,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        if (!canvas) {
            alert('{{ __("admin.error_cropping_image") }}');
            return;
        }
        
        // Convert canvas to blob
        canvas.toBlob(function(blob) {
            if (!blob) {
                alert('{{ __("admin.error_creating_image") }}');
                return;
            }
            
            // Create a new file from the blob
            const croppedFile = new File([blob], currentFile.name, {
                type: currentFile.type,
                lastModified: Date.now()
            });
            
            // Remove the original file from dropzone
            currentDropzone.removeFile(currentFile);
            
            // Add the cropped file (set flag to prevent re-triggering cropper)
            isCropping = true;
            currentDropzone.addFile(croppedFile);
            isCropping = false;
            
            // Update the file input
            let inputFile = document.querySelector(`input[name='${currentInputName}']`);
            if (!inputFile) {
                inputFile = document.createElement("input");
                inputFile.type = "file";
                inputFile.name = currentInputName;
                inputFile.classList.add("d-none");
                currentDropzone.element.closest("form").appendChild(inputFile);
            }
            
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            inputFile.files = dataTransfer.files;
            
            // Set up file removal handler
            currentDropzone.on("removedfile", function(file){
                const fileInput3 = document.querySelector(`input[name='${currentInputName}']`);
                if(fileInput3) fileInput3.value = "";
            });
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('imageCropperModal'));
            if (modal) {
                modal.hide();
            }
            
            // Destroy cropper
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
        }, currentFile.type, 0.9);
    });
    
    // Clean up cropper when modal is hidden
    document.getElementById('imageCropperModal')?.addEventListener('hidden.bs.modal', function() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        // Remove the file from dropzone if modal was closed without cropping
        if (currentFile && currentDropzone && isCropping) {
            currentDropzone.removeFile(currentFile);
            isCropping = false;
        }
        currentFile = null;
        currentDropzone = null;
        currentInputName = null;
    });
</script>

