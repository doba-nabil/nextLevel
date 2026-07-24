'use strict';
(function () {
    Dropzone.autoDiscover = false;
    const dropzoneBasic = document.querySelector('#dropzone-basic');
    if (dropzoneBasic) {
        const myDropzone = new Dropzone(dropzoneBasic, {
            url: "#",
            autoProcessQueue: false,
            uploadMultiple: false,
            maxFiles: 1,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here or click to upload"
        });

        myDropzone.on("addedfile", function (file) {
            if (myDropzone.files.length > 1) {
                myDropzone.removeFile(myDropzone.files[0]);
            }
            let inputFile = document.querySelector("input[name='image']");
            if (!inputFile) {
                inputFile = document.createElement("input");
                inputFile.type = "file";
                inputFile.name = "image";
                inputFile.classList.add("d-none");
                dropzoneBasic.closest("form").appendChild(inputFile);
            }
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            inputFile.files = dataTransfer.files;
        });

        myDropzone.on("removedfile", function () {
            const inputFile = document.querySelector("input[name='image']");
            if (inputFile) {
                inputFile.value = "";
            }
        });
    }
})();
