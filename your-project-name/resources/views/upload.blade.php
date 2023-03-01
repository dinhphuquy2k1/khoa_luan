<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap5.min.css') }}" rel="stylesheet" />

    <title>{{ config('app.name') }}</title>
</head>

<style>
    .card-footer,
    .progress {
        display: none;
    }
</style>

<body>

    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>Upload File Bài thi</h5>
                    </div>

                    <div class="card-body">
                        <div id="upload-container" class="text-center">
                            <button id="browseFile" class="btn btn-primary">Brows File</button>
                        </div>
                        <div class="progress mt-3" style="height: 25px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"
                                style="width: 75%; height: 100%">75%</div>
                        </div>
                    </div>

                    <div class="card-footer p-4">
                        <video id="videoPreview" src="" controls style="width: 100%; height: auto"></video>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header text-center">
                        <h5>Upload danh sách</h5>
                    </div>

                    <div class="card-body">
                        <div id="upload-container" class="text-center">
                            <button id="browseFile" class="btn btn-primary">Brows File</button>
                        </div>
                        <div class="progress mt-3" style="height: 25px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"
                                style="width: 75%; height: 100%">75%</div>
                        </div>
                    </div>

                </div>
                <div class="card">
                    <div class="card-header text-center">
                        <h5></h5>
                    </div>

                    <div class="card-body">
                        <div id="upload-container" class="text-center">
                            <button id="upload" class="btn btn-primary">Upload</button>
                        </div>
                        <div class="progress mt-3" style="height: 25px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"
                                style="width: 75%; height: 100%">75%</div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- jQuery -->
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <!-- Resumable JS -->
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    <script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
    <script type="text/javascript">
        let x = 0;
        let y = 0;
        let browseFile = $('#browseFile');
        let resumable = new Resumable({
            target: '/upload',
            query: {
                _token: '{{ csrf_token() }}',
                id: '501',
            }, // CSRF token
            headers: {
                'Accept': 'application/json'
            },
            simultaneousUploads: 5,
            testChunks: false,
            throttleProgressCallbacks: 1,
        });
        resumable.assignBrowse(browseFile[0]);

        resumable.on('fileAdded', function(file) { // trigger when file picked
            showProgress();
            resumable.upload() // to actually start uploading.
        });

        resumable.on('fileProgress', function(file) { // trigger when file progress update
            updateProgress(Math.floor(file.progress() * 100));
        });

        resumable.on('fileSuccess', function(file, response) { // trigger when file upload complete
            response = JSON.parse(response)
            $('#videoPreview').attr('src', response.path);
            $('.card-footer').show();
        });

        resumable.on('fileError', function(file, response) { // trigger when there is any error
            alert('file uploading error.')
        });


        let progress = $('.progress');

        function showProgress() {
            progress.find('.progress-bar').css('width', '0%');
            progress.find('.progress-bar').html('0%');
            progress.find('.progress-bar').removeClass('bg-success');
            progress.show();
        }

        function updateProgress(value) {
            progress.find('.progress-bar').css('width', `${value}%`)
            progress.find('.progress-bar').html(`${value}%`)
        }

        function hideProgress() {
            progress.hide();
        }

        $('#upload').click(function(e) {
            e.preventDefault();
            console.log(browseFile[0]);
            if (y == 2) {
                alert(1);
            }
        });
    </script>
</body>

</html>
