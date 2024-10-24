@empty($stok)
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Kesalahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
                    Data yang anda cari tidak ditemukan
                </div>
                <a href="{{ url('/stok') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/stok/' . $stok->stok_id . '/update_ajax') }}" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Data User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-control" required>
                            <option value="">- Pilih Supplier -</option>
                            @foreach ($supplier as $l)
                                <option {{ $l->supplier_id == $stok->supplier_id ? 'selected' : '' }}
                                    value="{{ $l->supplier_id }}">
                                    {{ $l->supplier_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error-supplier_id" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Barang</label>
                        <select name="barang_id" id="barang_id" class="form-control" required>
                            <option value="">- Pilih Barang -</option>
                            @foreach ($barang as $l)
                                <option {{ $l->barang_id == $stok->barang_id ? 'selected' : '' }}
                                    value="{{ $l->barang_id }}">
                                    {{ $l->barang_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error-barang_id" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>User</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">- Pilih User -</option>
                            @foreach ($user as $l)
                                <option {{ $l->user_id == $stok->user_id ? 'selected' : '' }} value="{{ $l->user_id }}">
                                    {{ $l->name }}</option>
                            @endforeach
                        </select>
                        <small id="error-user_id" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Stok tanggal</label>
                        <input value="{{ $stok->stok_tanggal->format('Y-m-d') }}" type="date" name="stok_tanggal" id="stok_tanggal"
                            class="form-control" required>
                        <small id="error-harga_jual" class="error-text form-text text-danger"></small>
                        <small id="error-stok_tanggal" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Stok jumlah</label>
                        <input value="{{ $stok->stok_jumlah }}" type="number" name="stok_jumlah" id="stok_jumlah"
                            class="form-control" required>
                        <small id="error-stok_jumlah" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </form>
    <script>
    $(document).ready(function() {
        $("#form-edit").validate({
            rules: {
                supplier_id: {
                    required: true,
                    number: true
                },
                barang_id: {
                    required: true,
                    number: true
                },
                user_id: {
                    required: true,
                    number: true
                },
                stok_tanggal: {
                    required: true,
                    date: true
                },
                stok_jumlah: {
                    required: true,
                    number: true,
                    min: 0
                }
            },
            messages: {
                supplier_id: {
                    required: "Supplier tidak boleh kosong",
                    number: "Format supplier tidak valid"
                },
                barang_id: {
                    required: "Barang tidak boleh kosong",
                    number: "Format barang tidak valid"
                },
                user_id: {
                    required: "User tidak boleh kosong",
                    number: "Format user tidak valid"
                },
                stok_tanggal: {
                    required: "Tanggal tidak boleh kosong",
                    date: "Format tanggal tidak valid"
                },
                stok_jumlah: {
                    required: "Jumlah stok tidak boleh kosong",
                    number: "Format jumlah harus berupa angka",
                    min: "Jumlah tidak boleh negatif"
                }
            },
            submitHandler: function(form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    beforeSend: function() {
                        $(form).find('button[type="submit"]').prop('disabled', true);
                        $(form).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Memperbarui...');
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#myModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            tableStok.ajax.reload();
                        } else {
                            $('.error-text').text('');
                            $.each(response.msgField, function(prefix, val) {
                                $('#error-' + prefix).text(val[0]);
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Gagal memperbarui data. Silakan coba lagi.'
                        });
                    },
                    complete: function() {
                        $(form).find('button[type="submit"]').prop('disabled', false);
                        $(form).find('button[type="submit"]').html('Simpan');
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
@endempty