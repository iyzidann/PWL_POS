@extends('layouts.template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Image -->
            <div class="card">
                <div class="card-body box-profile">
                    <div class="text-center">
                        @if ($user->avatar)
                            <img src="{{ asset('storage/foto_profil/' . $user->avatar) }}" class="profile-user-img img-fluid img-circle" alt="User profile picture">
                        @else
                            <i class="fas fa-user-circle fa-7x text-muted"></i>
                        @endif
                    </div>
                    
                    <div class="mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control border-right-0" readonly placeholder="No file chosen" id="file-name">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="browseButton">
                                    <i class="fas fa-folder-open mr-2"></i>Browse
                                </button>
                            </div>
                        </div>
                    </div>
            
                    <div class="mt-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-success" id="changeProfPicButton">Ganti Foto Profil</button>
                    </div>
            
                    <form id="profilePictureForm" action="{{ url('/profil/'.$user->id) }}" method="POST" style="display: none;" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="file" id="profilePictureInput" accept="image/*" name="foto_profil">
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
                    <form class="form-horizontal" method="POST" action="{{ url('/profil/'.$user->id) }}">
                        @csrf
                        @method('PUT') 
                    
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
                                <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}" required> 
                                @error('username') 
                                    <small class="form-text text-danger">{{ $message }}</small> 
                                @enderror 
                            </div> 
                        </div> 
                    
                        <div class="form-group row"> 
                            <label class="col-sm-3 col-form-label">Nama</label> 
                            <div class="col-9"> 
                                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $user->nama) }}" required> 
                                @error('nama') 
                                    <small class="form-text text-danger">{{ $message }}</small> 
                                @enderror 
                            </div> 
                        </div> 
                    
                        <div class="form-group row"> 
                            <label class="col-sm-3 col-form-label">Password</label> 
                            <div class="col-9"> 
                                <input type="password" class="form-control" id="password" name="password"> 
                                @error('password') 
                                    <small class="form-text text-danger">{{ $message }}</small> 
                                @else 
                                    <small class="form-text text-muted">Abaikan jika tidak ingin mengganti password.</small> 
                                @enderror 
                            </div> 
                        </div>
                        <div class="form-g  roup row">
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
        $('#browseButton').on('click', function() {
            $('#profilePictureInput').trigger('click');
        });

        $('#profilePictureInput').on('change', function() {
            var filename = $(this).val().split('\\').pop();
            $('#file-name').val(filename);
        });

        $('#changeProfPicButton').on('click', function() {
            $('#profilePictureForm').submit();
        });
    });
</script>
@endpush 