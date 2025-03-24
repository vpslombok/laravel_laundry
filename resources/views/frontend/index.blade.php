@extends('layouts.frontend')
@section('title','Selamat Datang')
@section('header')
@include('frontend.header')
@endsection
@section('banner')
{{-- banner --}}
@include('frontend.banner')
{{-- End banner --}}
@endsection

@section('content')
@include('frontend.content')
@endsection

@section('footer')
@include('frontend.footer')

{{-- Whatsapp Button Start--}}
<a href="https://wa.me/{{$setpage->whatsapp ?? ''}}" target="blank_">
  <img src="{{asset('frontend/img/wa.png')}}" class="wabutton" alt="WhatsApp-Button">
</a>
{{-- End: Whatsapp Button --}}
@endsection

@section('scripts')
<script type="text/javascript">
  // Status to steps mapping configuration
  const statusSteps = {
    'Proses Pencucian': {
      completed: [0], // Step 1 completed
      active: 1 // Step 2 active (washing)
    },
    'Siap Diambil': {
      completed: [0, 1], // Steps 1-2 completed
      active: 2 // Step 3 active (ready for pickup)
    },
    'DiTerima': {
      completed: [0, 1, 2], // Steps 1-3 completed
      active: 3 // Step 4 active (picked up)
    }
  };

  $(document).on('click', '.search-btn', function(e) {
    _curr_val = $('#search_status').val();
    $('#search_status').val(_curr_val + $(this).html());
  });

  function updateTimeline(status) {
    // Reset all steps
    $('.step_icon').removeClass('completed active');

    const steps = statusSteps[status];

    if (steps) {
      // Mark completed steps
      steps.completed.forEach(stepIndex => {
        $('.step_icon').eq(stepIndex).addClass('completed');
      });

      // Mark active step if exists
      if (steps.active !== null) {
        $('.step_icon').eq(steps.active).addClass('active');
      }
    }
  }

  $(document).on('click', '#search-btn', function(e) {
    var search_status = $("#search_status").val();

    if (!search_status) {
      swal({
        html: "Masukkan nomor resi terlebih dahulu!"
      });
      return;
    }

    $.get('pencarian-laundry', {
      '_token': $('meta[name=csrf-token]').attr('content'),
      search_status: search_status
    }, function(resp) {
      if (resp != 0) {
        // Update modal content
        $(".modal_status").show();
        $("#customer").html(resp.customer);
        $("#user").html(resp.user || '-');
        $("#no_resi").html(resp.invoice);
        $("#tgl_transaksi").html(resp.tgl_transaksi);
        $("#estimasi_selesai").html(resp.estimasi_selesai || '-');
        $("#tgl_ambil").html(resp.tgl_ambil || '-');
        $("#status_order").html(resp.status_order);
        $("#jenis_laundry").html(resp.jenis_laundry);

        // Update timeline visualization
        updateTimeline(resp.status_order);

      } else {
        swal({
          title: "Tidak Ditemukan",
          html: "No Resi <strong>" + search_status + "</strong> tidak terdaftar!",
          icon: "error"
        });
      }
    }).fail(function() {
      swal({
        title: "Error",
        text: "Terjadi kesalahan saat memproses permintaan",
        icon: "error"
      });
    });
  });

  function close_dlgs() {
    $(".modal_status").hide();
    $("#search_status").val("");
  }
</script>
@endsection