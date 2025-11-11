@extends('layouts.main')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <div class="card mb-4">
        <form action="{{ Route('editProfile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <h5 class="card-header">Detail Profil</h5>
            <!-- Account -->
            <div class="card-body">
                <div class="d-flex align-items-start align-items-sm-center gap-4 profile-wrapper">
                    <img src="{{ $user->foto_profile ? asset('storage/' . $user->foto_profile) : asset('assets/img/profile.jpg') }}"
                        alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                    <div class="button-wrapper">
                        <div class="upload">
                          <label for="foto_profile" class="btn jadwal-btn ml-2 mb-2" tabindex="0">
                              <span class="d-none d-sm-block">Upload foto baru</span>
                              <i class="bx bx-upload d-block d-sm-none"></i>
                              <input type="file" id="foto_profile" name="foto_profile" class="account-file-input" hidden
                                  accept="image/png, image/jpeg, image/jpg" />
                          </label>
                          
                          <button type="button" class="btn btn-outline-secondary account-image-reset mb-4 cancel-btn">
                              <i class="bx bx-reset d-block d-sm-none"></i>
                              <span class="d-none d-sm-block ">Reset</span>
                          </button>
                        </div>
                        <p class="text-muted content-center mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
                    </div>
                    <input type="hidden" name="reset_foto" id="reset_foto" value="0">
                </div>
            </div>

            <hr class="my-0" />
            <div class="card-body">
                <form id="formAccountSettings" method="POST" onsubmit="return false">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input class="form-control" type="text" id="nama" name="nama"
                                value="{{ ucfirst($user->name) }}" autofocus />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input class="form-control" type="text" id="email" name="email"
                                value="{{ $user->email }}" placeholder="john.doe@example.com" />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="noHP">No telpon</label>
                            <div class="input-group input-group-merge">
                                <input type="text" id="noHp" name="noHp" class="form-control"
                                    value="{{ $user->no_Hp }}" placeholder="0812345678" />
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Alamat"
                                value="{{ $user->alamat }}" />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="password" class="form-label">Password Baru (opsional)</label>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2 jadwal-btn">Save changes</button>
                        <button type="reset" class="btn btn-outline-secondary cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
    </div>
    <script>
        document.getElementById('foto_profile').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('uploadedAvatar');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
    <script>
        document.querySelector('.account-image-reset').addEventListener('click', function() {
            const fileInput = document.getElementById('foto_profile');
            const preview = document.getElementById('uploadedAvatar');
            const resetInput = document.getElementById('reset_foto');

            fileInput.value = ''; // reset input file
            preview.src = '{{ asset('assets/img/profile.jpg') }}'; // kembalikan ke default
            resetInput.value = '1';
        });
    </script>
@endsection
