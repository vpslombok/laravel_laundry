{{-- Banner --}}
    <div class="bg-cover">
    <img src="{{asset('frontend/img/banner.jpg')}}" alt="" />
    </div>
    <!-- end bg-cover -->
    <!-- begin container -->
    <div class="container">
        <h3>Lacak Status Laundry Kamu Disini...</h3>
        <div class="input-group m-b-20">
            <input type="number" class="form-control input-lg" id="search_status" placeholder="Input Nomor Resi Anda" />
            <span class="input-group-btn">
                <button type="submit" class="btn btn-lg btn-primary" id="search-btn"><i class="fa fa-search"></i></button>
            </span>
        </div>
        @include('frontend.modal')
    </div>
{{-- End Header --}}