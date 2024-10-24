@extends('layouts.template')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $page->title }}</h3>
        <div class="card-tools">
            <button onclick="modalAction('{{ url('/penjualan/import') }}')" class="btn btn-sm btn-info mt-1">Import Penjualan</button>
            <a class="btn btn-sm btn-primary mt-1" href="{{ url('/penjualan/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Penjualan</a>
            <a class="btn btn-sm btn-warning mt-1" href="{{ url('/penjualan/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Penjualan</a>
            <button onclick="modalAction('{{ url('penjualan/create_ajax') }}')" class="btn btn-sm btn-success mt-1">Tambah Data (Ajax)</button> 
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table class="table table-bordered table-striped table-hover table-sm" id="table_penjualan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>User</th>
                    <th>Pembeli</th>
                    <th>Penjualan Kode</th>
                    <th>Penjualan Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" databackdrop="static"
    data-keyboard="false" data-width="75%" aria-hidden="true"></div>
@endsection
@push('css')
@endpush
@push('js')
    <script>
        function modalAction(url = '') {
            $('#myModal').load(url, function () {
                $('#myModal').modal('show');
            });
        }
        var dataStok;
        $(document).ready(function () {
            dataPenjualan = $('#table_penjualan').DataTable({
                serverSide: true,
                ajax: {
                    "url": "{{ url('penjualan/list') }}",
                    "dataType": "json",
                    "type": "POST"
                },
                columns: [{
                    data: "DT_RowIndex",
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "user.nama",
                    classnama: "",
                    width: "10%",
                    orderable: true,
                    searchable: true
                },
                {
                    data: "pembeli",
                    classnama: "",
                    width: "30%",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "penjualan_kode",
                    classnama: "",
                    width: "15%",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "penjualan_tanggal",
                    classnama: "",
                    width: "15%",
                    orderable: true,
                    searchable: false,
                    render: function (data, type, row) {
                        if (data) {
                            var date = new Date(data);
                            var year = date.getFullYear();
                            var month = ("0" + (date.getMonth() + 1)).slice(-
                                2); // Add leading zero
                            var day = ("0" + date.getDate()).slice(-2); // Add leading zero
                            return year + "-" + month + "-" + day; // Format as YYYY-MM-DD
                        }
                        return data; // Return original value if no data
                    }
                },
                {
                    data: "aksi",
                    classnama: "text-center",
                    width: "25%",
                    orderable: false,
                    searchable: false
                }
                ]
            });
        });
    </script>
@endpush