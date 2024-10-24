@extends('layouts.template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Image -->
            <div class="card">
                <div class="card-body box-profile">
                    <div class="text-center">
                        @if(Auth::user()->avatar)
                            <img id="avatarPreview" src="{{ asset('storage/profil_pictures/' . Auth::user()->avatar) }}" style="width: 96px; height: 96px; border-radius: 50%;">
                        @else
                            <img id="avatarPreview" src="{{ asset('default-avatar.png') }}" style="width: 96px; height: 96px; border-radius: 50%;">
                        @endif
                        <p class="mb-0 mt-3">{{ Auth::user()->nama }}</p>
                    </div>

                    <form action="{{ url('/profil/avatar/' . Auth::user()->user_id) }}" method="POST" enctype="multipart/form-data" class="mt-3" id="formUpdateAvatar">
                        @csrf
                        @method('PATCH')
                        <div class="form-group mt-3">
                            <div class="input-group">
                                <input type="file" class="form-control border-right-0" id="avatarInput" name="avatar" style="display: none;">
                                <input type="text" class="form-control" readonly placeholder="No file chosen" id="avatarText">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary" type="button" id="browseButton">Browse</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">Ganti Foto Profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-3">
                    <h4 class="card-title">Edit Data Anda</h4>
                </div>
                <div class="card-body">
                    <form action="{{ url('/profil/account/' . Auth::user()->user_id) }}" method="POST" id="formUpdate">
                        @csrf
                        @method('PATCH')

                        <div class="form-group row">
                            <label for="levelPengguna" class="col-sm-3 col-form-label">Level Pengguna</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="level_id" name="level_id" required>
                                    <option value="">- Pilih Level -</option>
                                    @foreach($level as $item)
                                        <option value="{{ $item->level_id }}" @if($item->level_id == $user->level_id) selected @endif>{{ $item->level_nama }}</option>
                                    @endforeach
                                </select>
                                @error('level_id')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Username</label>
                            <div class="col-9">
                                <input value="{{ Auth::user()->username }}" type="text" name="username" id="username" class="form-control" required>
                                @error('username')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama</label>
                            <div class="col-9">
                                <input value="{{ Auth::user()->nama }}" type="text" name="nama" id="nama" class="form-control" required>
                                @error('nama')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Password</label>
                            <div class="col-9">
                                <input type="password" name="password" id="password" class="form-control">
                                @error('password')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @else
                                    <small class="form-text text-muted">Abaikan jika tidak ingin mengganti password.</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="offset-sm-3 col-sm-9">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Browse button functionality
        $('#browseButton').click(function() {
            $('#avatarInput').click();
        });

        $('#avatarInput').change(function() {
            var fileName = $(this).val().split('\\').pop();
            $('#avatarText').val(fileName);
        });

        // Update Avatar with Ajax
        $('#formUpdateAvatar').validate({
            rules: {
                avatar: {
                    required: true,
                    extension: 'jpg|jpeg|png'
                }
            },
            submitHandler: function(form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(function() {
                                // Refresh the page after successful update
                                location.reload();
                            });
                        } else {
                            $.each(response.msgField, function(prefix, val) {
                                $('#error-' + prefix).text(val[0]);
                            });
                        }
                    }
                });
                return false;
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        // Update Profile with Ajax
        $('#formUpdate').validate({
            rules: {
                username: {
                    required: true,
                    minlength: 3
                },
                nama: {
                    required: true,
                    maxlength: 100
                },
                password: {
                    minlength: 6
                }
            },
            submitHandler: function(form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(function() {
                                // Refresh the page after successful update
                                location.reload();
                            });
                        } else {
                            $.each(response.msgField, function(prefix, val) {
                                $('#error-' + prefix).text(val[0]);
                            });
                        }
                    }
                });
                return false;
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
@endpush
